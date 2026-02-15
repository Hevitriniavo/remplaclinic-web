import { initSummernote } from 'admin-app'

const { createApp, ref, computed, watchEffect, nextTick } = Vue

const app = createApp({
    setup() {
        const emailData = ref({
            to: '',
            cc: '',
            bcc: '',
            subject: '',
            html: true,
            bodyHtml: '',
            body: '',
        })

        const sentEnable = computed(() => {
            return emailData.value && emailData.value.subject
        })

        function initHtmlEditor() {
            initSummernote('#compose-textarea', {
                height: 350,
            })
        }

        function onCancelCompose() {
            $('#btn-inbox-list')[0].click()
        }

        function onValidateCompose(url) {
            if (emailData.value.html) {
                emailData.value.bodyHtml = $('#compose-textarea').summernote('code')
            }

            axios.post(url, {
                target: emailData.value.to,
                body: emailData.value.html ? emailData.value.bodyHtml : emailData.value.body,
                subject: emailData.value.subject,
                cc: emailData.value.cc,
                bcc: emailData.value.bcc,
                html: emailData.value.html,
            })
                .then(() => {
                    onCancelCompose()
                })
                .catch(() => {})
        }

        watchEffect(async () => {
            if (emailData.value.html) {
                await nextTick()
                
                initHtmlEditor()
            }
        })

        // onMounted(() => {
        //     // initHtmlEditor()
        // })

        return {
            emailData,
            sentEnable,
            onCancelCompose,
            onValidateCompose,
        }
    }
})


app.config.compilerOptions.delimiters = ["$%", "%$"]
app.mount("#root")
