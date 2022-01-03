import axios from 'axios'

export const http = axios.create({
  baseURL: process.env.VUE_API_BASE_URL || '/api',
  headers: {
    Accept: 'application/json'
  }
})

export function updateAuthorizationToken (token) {
  http.defaults.headers.common.Authorization = `Bearer ${token}`
}
