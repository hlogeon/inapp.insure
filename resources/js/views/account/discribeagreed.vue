<template>
      <div>
      <v-spinner v-if="loading" />
      <main v-else>
            <v-header />
            <section class="subscribe-section">
              <div class="container">
                <!-- wrap -->
                <div class="polis-wrap subscribe-wrap">
                  <div class="polis-content">
                    <h1 class="title">
                      Отказаться от полиса?
                    </h1>

                    <div class="subscribe__text-wrapper">
                      <p class="subscribe__text">
                        Ты нажал кнопку “отменить подписку”. Нам очень жаль, если ты
                        правда хочешь отказаться от своего полиса.
                      </p>

                      <p class="subscribe__text">
                        Если ты сделал это не специально, нажми кнопку “оставить все
                        как есть”, и твоя квартира по прежнему будет под защитой
                      </p>
                    </div>

                    <div class="subscribe-buttons">
                      <a href="javascript:;" class="button button--red" @click="canselSubscribe()">
                        Отказаться от полиса
                      </a>
                        <router-link to="/account" class="button">
                           Оставить все как есть
                        </router-link>
                    </div>
                  </div>

                  <div class="polis__img-wrapper">
                    <img src="/images/describagreed.svg" alt="" class="polis__img" />
                  </div>
                </div>
                <!-- ./ End of wrap -->
              </div>
              <!-- ./ End of container -->
            </section>
            <v-footer />
      </main>
   </div>
</template>

<script>

	export default {
		data:() => ({
         loading: true,
         success: "",
         error: ""
		}),
      mounted() {
         setTimeout(() => {
            this.loading = false
         }, 500)
      },
      methods: {
         canselSubscribe() {
            const data = new URLSearchParams()
            data.append('police_id', this.$route.params.id) 

            axios.get('/api/v1/cansel_subscribe', {params: data}).then(response => {
              if(response.data.status) {
                 this.$router.push('/discribvote/' + this.$route.params.id)
              } else
                 this.error = "Произошла ошибка во время отмены прописки"
            })
         }
      }
	}
</script>