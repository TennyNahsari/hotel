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
  
  let targetPath = path
  if (targetPath.includes('localhost:8000')) {
    const host = getApiHost()
    targetPath = targetPath.replace('http://localhost:8000', host)
  }
  
  if (targetPath.startsWith('http://') || targetPath.startsWith('https://')) {
    return targetPath
  }
  
  const host = getApiHost()
  const cleanPath = targetPath.startsWith('/') ? targetPath : '/' + targetPath
  if (cleanPath.startsWith('/storage/')) {
    return `${host}${cleanPath}`
  }
  return `${host}/storage${cleanPath}`
}
