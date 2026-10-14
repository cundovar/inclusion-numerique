import { defineStore } from 'pinia'
import { shuffleArray } from '../utils/aleatoir'



export const useQCMStore = defineStore('qcm', {
    state: () => ({
        qcms: [],
        answers: [],
        // Tentative déjà envoyée au serveur (évite un double enregistrement si la page de résultats est rouverte)
        attemptRecorded: false,
        filterQcms: {
            language: null,
            niveau: null,
        },
    }),
    actions: {
        setQcms(qcms) {
            this.qcms = qcms
            // Nouvelle série de questions : nouvelle tentative à enregistrer
            this.attemptRecorded = false
        },
        setAnswer(key, val) {
            this.answers[key] = val },
        markAttemptRecorded() {
            this.attemptRecorded = true
        },
        setFilterQcms(filterQcms) {
            this.filterQcms = filterQcms
        },
        //melanger les qcms dès l'origines
        AleatoireQCM(){
            this.qcms = shuffleArray(this.qcms)
        },
        //melanger les choix des qcms
        AleatoireChoices(){

            this.qcms.forEach(qcm => {
                if (qcm.choicesQCMs && qcm.choicesQCMs.length > 0) {
                    qcm.choicesQCMs = shuffleArray(qcm.choicesQCMs)
                }
            })
        },
        reset(){
            this.qcms = []
            this.answers = []
            this.attemptRecorded = false
            this.filterQcms = {
                language: null,
                niveau: null,
            }
        }
    },

     // Options de persistance
  persist: {
    storage: sessionStorage,
    paths: ['answers', 'filterQcms', 'attemptRecorded'] // on ne persiste que ces clés
  },
})
