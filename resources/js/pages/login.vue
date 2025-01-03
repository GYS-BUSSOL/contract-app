<script setup>
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { themeConfig } from '@themeConfig';
import { VForm } from 'vuetify/components/VForm';

definePage({
  meta: {
    layout: 'blank',
    public: true,
    unauthenticatedOnly: true,
  },
})
const route = useRoute()
const router = useRouter()
const ability = useAbility()

const credentials = ref({
  username: '',
  password: '',
  remember: false,
  captcha: ''
})
const refVForm = ref()
const captchaPath = ref()
const isPasswordVisible = ref(false)
const errors = ref({
  username: undefined,
  password: undefined,
  captcha: undefined
})
const loadingBtn = ref([])

const login = async () => {
  try {
    loadingBtn.value[0] = true
    const res = await $api('/auth/login', {
      method: 'POST',
      body: {
        username: credentials.value.username,
        password: credentials.value.password,
        captcha: credentials.value.captcha
      },
      onResponseError({ response }) {
        generateCaptcha()
        loadingBtn.value[0] = false
        errors.value = {username: response._data.message}
      },
    });
    const { accessToken, userData, userAbilityRules, status } = res
    if (status == 201) {
  
      useCookie('userAbilityRules').value = userAbilityRules
      ability.update(userAbilityRules)
      useCookie('userData').value = userData
      useCookie('accessToken').value = accessToken
      await nextTick(() => {
        router.replace(route.query.to ? String(route.query.to) : '/')
      })
    }
  } catch (err) {
    generateCaptcha()
    loadingBtn.value[0] = false
    throw new Error("Failed login");
  }
}

const generateCaptcha = async () => {
  try {
    const response = await $api('/generate-captcha', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      onResponseError({ response }) {
        throw new Error("Get data failed");
      },
    });

    const responseStringify = JSON.stringify(response);
    const responseParse = JSON.parse(responseStringify);

    if(responseParse?.status == 200) {
      captchaPath.value = responseParse?.data.img;
    } else {
      throw new Error("Get data failed");
    }
  } catch (error) {
    throw new Error("Get data failed");
  }
}

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid)
      login()
  })
}

onMounted(() => {
  generateCaptcha()
});
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- Auth Card -->
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
      >
        <VCardItem class="justify-center">
          <VCardTitle>
            <RouterLink to="/login">
              <div class="">
                <VNodeRenderer :nodes="themeConfig.app.logoLogin" />
                <h5 class="app-logo-title">
                  <!-- {{ themeConfig.app.title }} -->
                </h5>
              </div>
            </RouterLink>
          </VCardTitle>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            Contract Management System
          </h4>
          <p class="mb-0">
            Please sign-in to your account
          </p>
        </VCardText>

        <VCardText>
          <VForm 
            ref="refVForm"
            @submit.prevent="onSubmit">
            <VRow>
              <!-- username -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.username"
                  placeholder="Username"
                  autofocus
                  label="Username"
                  type="text"
                  :rules="[requiredValidator]"
                  :error-messages="errors.username"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :rules="[requiredValidator]"
                  :error-messages="errors.password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
                <div class="d-flex gap-5 mt-5">
                  <div class="d-flex flex-column">
                    <div>
                      <img :src="captchaPath"/>
                    </div>
                    <div>
                      <small class="text-primary cursor-pointer" @click="generateCaptcha()">
                        <VIcon
                          icon="tabler-reload"
                          start
                          class="flip-in-rtl"
                        />
                        Reload captcha
                      </small>
                    </div>
                  </div>
                  <div style="width: 100%;">
                    <AppTextField
                      v-model="credentials.captcha"
                      placeholder="Type here..."
                      type="text"
                      :rules="[requiredValidator]"
                      :error-messages="errors.captcha"
                    />
                  </div>
                </div>
                <!-- remember me checkbox -->
                <div class="d-flex align-center justify-space-between flex-wrap my-6">
                  <VCheckbox
                    v-model="credentials.remember"
                    label="Remember me in 7 days"
                  />
                </div>

                <!-- login button -->
                <VBtn
                  block
                  type="submit"
                  :loading="loadingBtn[0]"
                  :disabled="loadingBtn[0]"
                >
                  Login
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
