import axios from 'axios'
import showToast from './rempla-toaster.js'

const myInstance = axios.create({})

myInstance.interceptors.response.use(
  function (response) {
    return response
  },
  function (error) {
    console.error('API_ERROR: ', error.response?.data || error)

    // check if error is an api error
    const message = error.response?.data?.error
    if (message) {
      showToast(message, 'error')
    }

    return Promise.reject(error.response?.data || error)
  }
)

export default myInstance