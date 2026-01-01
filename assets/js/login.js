const { createApp, ref, onMounted } = Vue

const LoginForm = {
    props: [
        'url',
    ],
    template: '<slot></slot>',
    setup(props) {
        const email = ref('')
        const password = ref('')
        const errorMessage = ref('')

        const submitLogin = async (e) => {
            e.preventDefault()

            console.log('Submitting login for: ', { email: email.value, password: password.value })

            errorMessage.value = ''
            try {
                const response = await axios.post(props.url, {
                    email: email.value,
                    password: password.value,
                })
                // Handle successful login (e.g., redirect)
                console.log('Login successful:', response.data)
            } catch (error) {
                if (error.response && error.response.status === 401) {
                    errorMessage.value = 'Invalid credentials. Please try again.'
                } else {
                    errorMessage.value = 'An error occurred. Please try again later.'
                }
            }
        }

        onMounted(() => {
            console.log('PROPS: ', props)
            console.log('LoginForm mounted')
        })

        return {
            email,
            password,
            errorMessage,
            submitLogin,
        }
    },
}

const app = createApp({})

app.component('login-form', LoginForm)

app.mount('#login-root')