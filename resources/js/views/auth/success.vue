<template>
	<div>
		<v-spinner v-if="loading" />
		<template v-else>
			<v-header></v-header>
			<section class="step-section">
    		  <div class="container">
    		    <!-- wrap -->
    		    <div class="step-wrap">
    		      	

    		      <h1 class="title">
                <a @click="previousPage" class="button button-arrow button-arrow--prev">
                  <svg class="svg">
                    <use xlink:href="#arrow"></use>
                  </svg>
                </a>
    		        Твой полис оплачен <br />
    		        Куда отправить чек об оплате?
    		      </h1>

    		      <!-- form -->
    		      <div class="step-form">
    		        <div class="step-form__email">
    		          <div class="input-wrapper">
    		            <label class="input-label">
    		              <input required class="input input--big email" v-model="email" type="email" placeholder="Твой e-mail" />
    		              <div class="input__placeholder">
    		                <span>Твой e-mail</span>
    		              </div>
    		            </label>
    		          </div>
    		        </div>
                <div class="error" v-for="value in errors">{{ showError(value) }}</div>
    		        <button class="button submit-button" :disabled="disabled" type="button" v-on:click="sendEmail()">
    		          Продолжить
    		        </button>
    		      </div>
    		      <!-- ./ End of form -->
    		    </div>
    		    <!-- ./ End of wrap -->
    		  </div>
    		  <!-- ./ End of container -->
    		</section>
		</template>
	</div>
	
</template>
<script>
	import axios from "axios"
	import cookies from 'vue-cookie'
	import mixinemail from '../../mixins/email'

	export default {
		data:() => ({
			loading: true,
			email: "",
			errors: [],
			nextStep: "",
      disabled: true
		}),
		mixins: [mixinemail],
		mounted: function() {
			const data = new URLSearchParams()
      data.append('action', 'checking')
			axios.get('/api/v1/send_phone', {params: data}).then(response => {
			    console.log('response', response)
        	if(response.data.data.hasOwnProperty('email') && response.data.data.email) {
        	  	this.email = response.data.data.email
        	  	this.sendEmail()
        	} else {
        		setTimeout(() => {
      		  	this.loading = false
      		}, 500)
      		setTimeout(() => {
      		  	document.body.click()
              this.disabled = false
      		}, 1000)
        	}
      })
      this.onSubmit()
		},
		methods: {
			previousPage() {
        if(this.$route.query.hasOwnProperty('previously'))
        	this.$router.push({
        	    path:"/authaddress",
        	    query: this.$route.query
        })
      	else
      	  	this.$router.go(-1)
      },
			sendEmail() {

				this.errors = []
				if(this.email.length == 0) {
					this.errors.push({email: "Вы не ввели email"})
				}
				else if(!this.validateEmail(this.email))
					this.errors.push({email: "Вы ввели некорректный email"})

        this.isInvalid('email')

				if(this.errors.length == 0) {
          this.disabled = true
					const data = new URLSearchParams()
					data.append('email', this.email)
					data.append('action', 'email')

					axios.get('/api/v1/send_phone', {params: data}).then(response => {
						if(response.data.status) {
							if(response.data.data.user_is_activated)
								this.$router.push("/authactive")
								
						} else {
							if(response.data.data.hasOwnProperty('errors'))
								response.data.data.errors.forEach(($error) => {
									this.errors.push($error)
								})
						}
            this.disabled = false
            this.isInvalid('email')
					})
				}
			},
		}
	}
</script>