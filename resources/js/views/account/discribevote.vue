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
                  Мы рады, что ты был с нами!
                </h1>

                <div class="subscribe__text-wrapper">
                  <p class="subscribe__text">
                    И прежде чем уйти, мы просим тебя написать в одной строке,
                    почему же ты отказываешься от нашего полиса
                  </p>
                </div>

                <div class="input-wrapper">
                  <label class="input-label">
                    <input
                      required
                      class="input input--big"
                      type="text"
                      placeholder="Я отписываюсь, потому что:"
                      v-model="vote"
                    />
                    <div class="input__placeholder">
                      <span>Я отписываюсь, потому что:</span>
                    </div>
                  </label>
                </div>

                <div class="subscribe-buttons">
                  <a href="javascript:;" class="button" @click="canselVote()">
                    Оставить отзыв
                  </a>
                </div>
              </div>

              <div class="polis__img-wrapper">
                <img src="/images/desubscribevote.svg" alt="" class="polis__img" />
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
         vote: "",
         success: "",
         error: ""
    }),
    mounted() {
       setTimeout(() => {
          this.loading = false
       }, 500)
    },
    methods: {
       canselVote() {
          const data = new URLSearchParams()
          data.append('police_id', this.$route.params.id)
          data.append('vote', this.vote) 

          axios.get('/api/v1/cansel_vote', {params: data}).then(response => {
             if(response.data.status) {
                this.$router.push('/account')
             } else
                this.error = "Произошла ошибка во время обработки вашего отзыва"
          })
       }
    }
  }
</script>