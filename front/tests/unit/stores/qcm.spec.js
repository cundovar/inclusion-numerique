import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useQCMStore } from '@/stores/qcm'

// US-06 / US-07 : questions du QCM, réponses et remise à zéro
const qcms = [
  { id: 1, choicesQCMs: [{ id: 11 }, { id: 12 }, { id: 13 }, { id: 14 }] },
  { id: 2, choicesQCMs: [{ id: 21 }, { id: 22 }] },
  { id: 3, choicesQCMs: [] }
]
const ids = (list) => list.map((item) => item.id).sort()

describe('store QCM', () => {
  beforeEach(() => setActivePinia(createPinia()))

  it('enregistre la réponse donnée à chaque question', () => {
    const store = useQCMStore()

    store.setAnswer(0, 12)
    store.setAnswer(1, 21)

    expect(store.answers[0]).toBe(12)
    expect(store.answers[1]).toBe(21)
  })

  it('mélange les questions sans en perdre ni en ajouter', () => {
    const store = useQCMStore()
    store.setQcms(structuredClone(qcms))

    store.AleatoireQCM()

    expect(ids(store.qcms)).toEqual([1, 2, 3])
  })

  it('mélange les choix de chaque question sans les modifier', () => {
    const store = useQCMStore()
    store.setQcms(structuredClone(qcms))

    store.AleatoireChoices()

    store.qcms.forEach((qcm) => {
      const original = qcms.find((q) => q.id === qcm.id)
      expect(ids(qcm.choicesQCMs)).toEqual(ids(original.choicesQCMs))
    })
  })

  it('remet le QCM à zéro', () => {
    const store = useQCMStore()
    store.setQcms(structuredClone(qcms))
    store.setAnswer(0, 12)
    store.setFilterQcms({ language: 'CSS', niveau: 'basique' })

    store.reset()

    expect(store.qcms).toEqual([])
    expect(store.answers).toEqual([])
    expect(store.filterQcms).toEqual({ language: null, niveau: null })
  })
})
