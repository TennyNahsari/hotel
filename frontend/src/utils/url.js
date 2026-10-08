/**
 * URL Helper Utilities for API Host & Storage Asset Resolution
 */

export function getApiHost() {
  const envUrl = import.meta.env.VITE_API_URL
  if (envUrl && envUrl !== 'http://localhost:8000') {
    return envUrl.replace(/\/$/, '')
  }
  
  if (typeof window !== 'undefined' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
    return window.location.origin
  }
  
  return envUrl || 'http://localhost:8000'
}

export function getStorageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }
  
  const host = getApiHost()
  const cleanPath = path.startsWith('/') ? path : '/' + path
  if (cleanPath.startsWith('/storage/')) {
    return `${host}${cleanPath}`
  }
  return `${host}/storage${cleanPath}`
}
