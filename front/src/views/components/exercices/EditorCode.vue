<!-- MonacoPlayground.vue -->
<template>
    <section
    class="transition-all duration-500"
    :class="sectionSize ? 'playground-fullscreen' : 'playground'">

      <div class="cols">

        <button v-if="sectionSize"
          class="absolute top-2 right-2 w-8 h-8 bg-red-600 hover:bg-red-700 text-white rounded flex items-center justify-center z-10 cursor-pointer"
          @click="toggleSectionSize"
        >
          ✕
        </button>
        <button v-else
          class="absolute top-2 right-2 p-2 bg-red-600 hover:bg-red-700 text-white rounded flex items-center justify-center z-10 cursor-pointer"
          @click="toggleSectionSize"
        >
          agrandire
        </button>
            <!-- Colonne éditeurs -->
            <div class="left">
          <div class="editor-block">
            <header class="hdr">
              <h2>HTML</h2><small>html</small>
            </header>
            <div ref="htmlEl" class="editor-host"></div>
          </div>
  
          <div class="editor-block">
            <header class="hdr">
              <h2>CSS</h2><small>css</small>
            </header>
            <div ref="cssEl" class="editor-host"></div>
          </div>
  
          <div class="editor-block">
            <header class="hdr">
              <h2>JavaScript</h2><small>javascript</small>
            </header>
            <div ref="jsEl" class="editor-host"></div>
          </div>
  
          <div class="toolbar">
            <button type="button" @click="rebuild">Exécuter ▶</button>
            <label><input type="checkbox" v-model="autorun"> Auto</label>
            <button type="button" @click="resetAll">Tout réinitialiser</button>
          </div>
        </div>
  
        <!-- Colonne preview + console -->
        <div class="right ">
          <h2 class="title">Prévisualisation</h2>
          <div class="preview">
            <iframe ref="frame" sandbox="allow-scripts"></iframe>
          </div>
  
          <h2 class="title">Console</h2>
          <div class="console">
            <div v-if="logs.length === 0" class="muted">(aucune sortie)</div>
            <div v-for="(line, i) in logs" :key="i" class="log">
              <span :class="['tag', line.type]">[{{ line.type.toUpperCase() }}]</span>
              <span class="msg">{{ line.msg }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script setup>
  import { ref, onMounted, onBeforeUnmount } from 'vue'
  import loader from '@monaco-editor/loader'
  
  /**
   * Props optionnelles pour personnaliser les valeurs de départ et la persistance.
   * - slug: si fourni, on persiste dans localStorage (exo:<slug>:{html|css|js})
   */
  const props = defineProps({
    initialHtml: { type: String, default: '<h1>Hello </h1>\n<p>Modifie ce HTML</p>' },
    initialCss:  { type: String, default: 'body{font-family:system-ui,sans-serif;padding:16px;}\nh1{font-size:2rem}' },
    initialJs:   { type: String, default: 'console.log("JS prêt 🚀")\n// Écris du JS ici\n' },
    autorunDefault: { type: Boolean, default: true },
    slug: { type: String, default: '' }
  })
  
  // 👉 Dé-commente si tu veux éviter toute config Vite des workers en local.
  // loader.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.0/min/vs' } })
  
  // Helpers persistance locale
  const hasSlug = !!props.slug
  const k = (part) => `exo:${props.slug}:${part}`
  const load = (part, fallback) => hasSlug ? (localStorage.getItem(k(part)) ?? fallback) : fallback
  const save = (part, val) => { if (hasSlug) localStorage.setItem(k(part), val) }
  
  // Refs DOM
  const htmlEl = ref(null)
  const cssEl  = ref(null)
  const jsEl   = ref(null)
  const frame  = ref(null)
  
  // Monaco + éditeurs
  let monaco = null
  let htmlEditor = null
  let cssEditor  = null
  let jsEditor   = null
  
  // Etats UI
  const autorun = ref(props.autorunDefault)
  const logs = ref([])
  function pushLog(type, msg){ logs.value.push({ type, msg: String(msg) }) }
  function clearLogs(){ logs.value = [] }
  
  // Timer debounce
  let timer = null
  function scheduleRebuild(){
    if (!autorun.value) return
    clearTimeout(timer)
    timer = setTimeout(rebuild, 500)
  }

  //resize section
  const sectionSize= ref(false)
  const toggleSectionSize = () => {
    sectionSize.value = !sectionSize.value
  }


  
  // Générer le srcdoc
  function buildSrcdoc(){
    const html = htmlEditor?.getValue() ?? props.initialHtml
    const css  = cssEditor?.getValue()  ?? props.initialCss
    const js   = jsEditor?.getValue()   ?? props.initialJs

    const consoleHook = `
  <${'script'}>
  (function(){
    const send=(type,args)=>{
      parent.postMessage({
        source:'exo-playground',
        type,
        msg: args.map(a=>{try{return typeof a==='object'?JSON.stringify(a):String(a)}catch(e){return String(a)}}).join(' ')
      }, '*');
    };
    ['log','info','warn','error'].forEach(fn=>{
      const orig=console[fn];
      console[fn]=function(){ send(fn, Array.from(arguments)); orig.apply(console, arguments); };
    });
    window.addEventListener('error', e=>{
      parent.postMessage({source:'exo-playground', type:'error', msg:(e.message||'Erreur JS')+' @'+(e.filename||'')+':'+(e.lineno||'')}, '*');
    });
  })();
  </${'script'}>`.trim()

    const userJs = `
  <${'script'}>
  try {
  ${js}
  } catch(e) {
    console.error(e && e.stack ? e.stack : e);
  }
  </${'script'}>`.trim()
  
    return `<!doctype html>
  <html>
  <head>
  <meta charset="utf-8">
  <style>
  ${css}
  </style>
  </head>
  <body>
  ${html}
  ${consoleHook}
  ${userJs}
  </body>
  </html>`
  }
  
  // Rebuild iframe
  function rebuild(){
    clearLogs()
    if (frame.value) frame.value.srcdoc = buildSrcdoc()
    // Sauvegarde si slug
    if (hasSlug){
      htmlEditor && save('html', htmlEditor.getValue())
      cssEditor  && save('css',  cssEditor.getValue())
      jsEditor   && save('js',   jsEditor.getValue())
    }
  }
  
  // Reset
  function resetAll(){
    htmlEditor?.setValue(props.initialHtml)
    cssEditor?.setValue(props.initialCss)
    jsEditor?.setValue(props.initialJs)
    rebuild()
  }
  
  // Réception logs
  function onMsg(e){
    if (!e?.data || e.data.source !== 'exo-playground') return
    pushLog(e.data.type, e.data.msg)
  }
  
  onMounted(async () => {
    window.addEventListener('message', onMsg)
    monaco = await loader.init()
  
    // Valeurs initiales (avec persistance si slug)
    const startHtml = load('html', props.initialHtml)
    const startCss  = load('css',  props.initialCss)
    const startJs   = load('js',   props.initialJs)
  
    // Création des 3 éditeurs
    htmlEditor = monaco.editor.create(htmlEl.value, {
      value: startHtml,
      language: 'html',
      automaticLayout: true,
      theme: 'vs-dark',
      minimap: { enabled: false },
      fontSize: 13,
      scrollBeyondLastLine: false,
      tabSize: 2
    })
    cssEditor = monaco.editor.create(cssEl.value, {
      value: startCss,
      language: 'css',
      automaticLayout: true,
      theme: 'vs-dark',
      minimap: { enabled: false },
      fontSize: 13,
      scrollBeyondLastLine: false,
      tabSize: 2
    })
    jsEditor = monaco.editor.create(jsEl.value, {
      value: startJs,
      language: 'javascript',
      automaticLayout: true,
      theme: 'vs-dark',
      minimap: { enabled: false },
      fontSize: 13,
      scrollBeyondLastLine: false,
      tabSize: 2
    })
  
    // Autorun sur modifications
    htmlEditor.onDidChangeModelContent(scheduleRebuild)
    cssEditor.onDidChangeModelContent(scheduleRebuild)
    jsEditor.onDidChangeModelContent(scheduleRebuild)
  
    // Premier rendu
    rebuild()
  })
  
  onBeforeUnmount(() => {
    window.removeEventListener('message', onMsg)
    htmlEditor?.dispose()
    cssEditor?.dispose()
    jsEditor?.dispose()
  })
  </script>
  
  <style scoped>
  * {
    box-sizing: border-box;
  }

  .playground {
    position: relative;
    width: 100%;
    margin: 0 auto;
    padding: 16px;
  }

  /* Mode plein écran */
  .playground-fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    background: #fff;
    padding: 16px;
    margin: 0;
    overflow: auto;
  }

  .cols {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    width: 100%;
  }

  @media (min-width: 900px) {
    .cols {
      grid-template-columns: 1fr 1fr;
    }
  }

  /* Force le layout en plein écran */
  .playground-fullscreen .cols {
    grid-template-columns: 1fr 1fr;
  }

  .left,
  .right {
    width: 100%;
    min-width: 0;
  }

  .left .editor-block {
    display: flex;
    flex-direction: column;
    width: 100%;
  }
  .hdr { display: flex; align-items: center; justify-content: space-between; }
  .editor-host {
    height: 230px;
    border: 1px solid #d0d0d0;
    border-radius: 8px;
    overflow: hidden;
    width: 100%;
  }

  /* Éditeurs en mode fullscreen */
  .playground-fullscreen .editor-host {
    height: 280px;
  }
  .toolbar { display:flex; gap: 8px; align-items: center; }
  .toolbar button { padding: 8px 12px; border-radius: 6px; border: 1px solid #d0d0d0; background: #0b63f6; color:#fff; }
  .toolbar button:nth-child(3) { background:#f3f3f3; color:#222; }
  .right .title { margin: 8px 0; }
  .preview { border: 1px solid #d0d0d0; border-radius: 8px; overflow: hidden; }
  .preview iframe { width: 100%; height: 360px; background: #fff; display:block; }
  .console { border: 1px solid #d0d0d0; border-radius: 8px; background: #111; color:#fff; padding: 8px; height: 220px; overflow:auto; font: 12px/1.4 ui-monospace, SFMono-Regular, Menlo, monospace; }
  .console .muted { opacity: .6; }
  .log { white-space: pre-wrap; margin: 4px 0; }
  .tag { margin-right: 6px; }
  .tag.error { color: #ff6b6b; }
  .tag.warn  { color: #ffd166; }
  .tag.log, .tag.info { color: #3ad29f; }
  </style>
  