import axios from 'axios'

// NOTE: Changed baseURL to /api because Auth routes are at root /api, not inside v1
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true
})

export { api }
