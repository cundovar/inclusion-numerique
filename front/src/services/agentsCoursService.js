import axios from 'axios'

const envUrl = import.meta.env.VITE_AGENTS_API_URL || '/api/agents-cours'
const baseURL = envUrl.endsWith('/') ? envUrl : envUrl + '/'

const agentsApi = axios.create({
  baseURL,
  timeout: 300000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

function extractData(response) {
  return response?.data?.data ?? response?.data ?? null
}

export async function listerTechnologies() {
  const response = await agentsApi.get('technologies')
  return extractData(response) || []
}

export async function listerNiveaux() {
  const response = await agentsApi.get('niveaux')
  return extractData(response) || []
}

export async function listerMenus() {
  const response = await agentsApi.get('menus')
  return extractData(response) || []
}

export async function creerCours(payload) {
  const response = await agentsApi.post('cours/creer', payload)
  return extractData(response)
}

export async function reviserCours(payload) {
  const response = await agentsApi.post('cours/reviser', payload)
  return extractData(response)
}

export async function getCours(id) {
  const response = await agentsApi.get(`cours/${id}`)
  return extractData(response)
}

export async function listCours(params = {}) {
  const response = await agentsApi.get('cours', { params })
  return extractData(response) || []
}

export async function listRevisions(courseId) {
  const response = await agentsApi.get(`cours/${courseId}/revisions`)
  return extractData(response) || []
}

export async function appliquerRevision(revisionId) {
  const response = await agentsApi.post(`revisions/${revisionId}/appliquer`)
  return extractData(response)
}

export async function updateRevision(revisionId, payload) {
  const response = await agentsApi.patch(`revisions/${revisionId}`, payload)
  return extractData(response)
}

export { agentsApi }
