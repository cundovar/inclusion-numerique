import { ref, computed, onBeforeUnmount } from 'vue'

// Lecture à voix haute d'un contenu HTML avec la synthèse vocale du navigateur (Web Speech API).
// Aucun service externe : rien n'est envoyé hors de l'appareil et la lecture reste possible hors ligne
// si une voix locale est installée.

const BLOCK_SELECTOR = 'h1,h2,h3,h4,h5,h6,p,li,blockquote,figcaption,td,th,dt,dd'
const IGNORED_SELECTOR = 'pre,code,script,style,svg,[aria-hidden="true"],[data-no-read]'
const MAX_CHUNK = 220
const RATE_KEY = 'mediateur:readAloud:rate'

export const RATES = [
  { value: 0.8, label: 'Lent' },
  { value: 1, label: 'Normal' },
  { value: 1.2, label: 'Rapide' }
]

function normalize(text) {
  return text.replace(/\s+/g, ' ').trim()
}

// Les navigateurs coupent parfois les longs énoncés : on découpe par phrases.
export function splitSentences(text) {
  const sentences = text.match(/[^.!?…]+[.!?…]*\s*/g) ?? [text]
  const chunks = []
  let current = ''

  for (const sentence of sentences) {
    if (current && (current + sentence).length > MAX_CHUNK) {
      chunks.push(current.trim())
      current = ''
    }
    current += sentence
  }
  if (current.trim()) {
    chunks.push(current.trim())
  }

  return chunks
}

// Extrait les passages à lire dans l'ordre du document : titres, paragraphes, listes, légendes.
// Les blocs de code et les éléments décoratifs sont ignorés.
export function extractReadableChunks(root) {
  if (!root) {
    return []
  }

  const blocks = [...root.querySelectorAll(BLOCK_SELECTOR)].filter(
    (el) => !el.closest(IGNORED_SELECTOR) && !el.querySelector(BLOCK_SELECTOR)
  )

  return blocks.flatMap((el) => {
    const clone = el.cloneNode(true)
    clone.querySelectorAll(IGNORED_SELECTOR).forEach((node) => node.remove())
    const text = normalize(clone.textContent ?? '')
    return text ? splitSentences(text) : []
  })
}

function pickFrenchVoice(voices) {
  const french = voices.filter((voice) => voice.lang?.toLowerCase().startsWith('fr'))
  if (french.length === 0) {
    return null
  }

  const score = (voice) =>
    (voice.lang.toLowerCase() === 'fr-fr' ? 4 : 0) +
    (/natural|neural|premium|enhanced/i.test(voice.name) ? 3 : 0) +
    (voice.localService ? 1 : 0)

  return [...french].sort((a, b) => score(b) - score(a))[0]
}

function loadRate() {
  try {
    const saved = Number(localStorage.getItem(RATE_KEY))
    return RATES.some((rate) => rate.value === saved) ? saved : 1
  } catch {
    return 1
  }
}

export function useReadAloud(targetRef) {
  const supported = typeof window !== 'undefined' && 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window
  // 'idle' | 'playing' | 'paused'
  const status = ref('idle')
  const rate = ref(loadRate())
  const progress = ref({ current: 0, total: 0 })

  let chunks = []
  let index = 0
  // Incrémenté à chaque (re)démarrage pour ignorer les événements d'une lecture annulée
  let session = 0

  const isPlaying = computed(() => status.value === 'playing')
  const isPaused = computed(() => status.value === 'paused')

  function speakCurrent() {
    if (index >= chunks.length) {
      finish()
      return
    }

    const mySession = session
    const utterance = new SpeechSynthesisUtterance(chunks[index])
    const voice = pickFrenchVoice(window.speechSynthesis.getVoices())

    utterance.lang = voice?.lang ?? 'fr-FR'
    if (voice) {
      utterance.voice = voice
    }
    utterance.rate = rate.value

    utterance.onend = () => {
      if (mySession !== session) {
        return
      }
      index += 1
      progress.value = { current: Math.min(index, chunks.length), total: chunks.length }
      speakCurrent()
    }
    utterance.onerror = (event) => {
      if (mySession !== session || event.error === 'canceled' || event.error === 'interrupted') {
        return
      }
      finish()
    }

    window.speechSynthesis.speak(utterance)
  }

  function finish() {
    session += 1
    chunks = []
    index = 0
    status.value = 'idle'
    progress.value = { current: 0, total: 0 }
  }

  function start() {
    if (!supported) {
      return
    }

    window.speechSynthesis.cancel()
    session += 1
    chunks = extractReadableChunks(targetRef.value)
    index = 0

    if (chunks.length === 0) {
      status.value = 'idle'
      return
    }

    progress.value = { current: 0, total: chunks.length }
    status.value = 'playing'
    speakCurrent()
  }

  function pause() {
    if (!supported || status.value !== 'playing') {
      return
    }
    window.speechSynthesis.pause()
    status.value = 'paused'
  }

  function resume() {
    if (!supported || status.value !== 'paused') {
      return
    }
    window.speechSynthesis.resume()
    status.value = 'playing'
  }

  function stop() {
    if (!supported) {
      return
    }
    window.speechSynthesis.cancel()
    finish()
  }

  // Chrome ne reprend pas toujours après pause() : on relit simplement le passage en cours.
  function setRate(value) {
    rate.value = value
    try {
      localStorage.setItem(RATE_KEY, String(value))
    } catch {
      // Stockage indisponible : le réglage vaut pour la page seulement.
    }

    if (status.value === 'idle') {
      return
    }

    const wasPaused = status.value === 'paused'
    window.speechSynthesis.cancel()
    session += 1
    status.value = 'playing'
    speakCurrent()
    if (wasPaused) {
      // Relancer en pause serait incohérent : la lecture reprend au nouveau rythme.
      window.speechSynthesis.resume()
    }
  }

  function toggle() {
    if (status.value === 'idle') {
      start()
    } else if (status.value === 'playing') {
      pause()
    } else {
      resume()
    }
  }

  if (supported) {
    window.addEventListener('pagehide', stop)
  }

  onBeforeUnmount(() => {
    if (supported) {
      window.removeEventListener('pagehide', stop)
      window.speechSynthesis.cancel()
      session += 1
    }
  })

  return { supported, status, isPlaying, isPaused, rate, progress, start, pause, resume, stop, toggle, setRate }
}
