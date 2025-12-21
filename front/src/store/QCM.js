import { defineStore } from 'pinia'
import { shuffleArray } from '../utlis/aleatoir'



export const useQCMStore = defineStore('qcm', {
    state: () => ({
        qcms: [],
        answers: [],
        filterQcms: {
            language: null,
            niveau: null,
        },
    }),
    actions: {
        setQcms(qcms) {
            this.qcms = qcms
        },
        setAnswer(key, val) { 
            this.answers[key] = val },
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
            this.filterQcms = {
                language: null,
                niveau: null,
            }
        }
    },

     // ⬇️ Options de persistance
  persist: {
    storage: sessionStorage,
    paths: ['answers', 'filterQcms'] // on ne persiste que ces clés
    // persist: true  // (alternative rapide pour tout persister)
  },
})