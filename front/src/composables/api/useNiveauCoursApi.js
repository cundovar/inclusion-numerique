import api from '@/services/api'


export function useNIveauCourApi(){
    const getNiveaux=async()=>{
        const response=await api.get('/niveau_cours')
        // API Platform peut retourner 'hydra:member' ou 'member' selon la config
        const result = response.data['hydra:member'] || response.data['member'] || response.data

        return result
    }

    return {
        getNiveaux
    }
}