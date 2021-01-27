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
			        	<router-link to="/changephone" class="button button-arrow button-arrow--prev">
           				  	<svg class="svg">
           				  	  <use xlink:href="#arrow"></use>
           				  	</svg>
           				</router-link>
			          	Введи код из SMS
			        </h1>

			        <p class="step__text for-desktop">
			          	Мы отправили проверочный код на номер {{ phone }}
			        </p>

			        <!-- form -->
			        <div class="step-form">
			        	
			          <div class="input-wrapper input-wrapper--code">
			            <label class="input-label">
			              <input class="input sms" v-model="sms" type="text" maxlength="4" placeholder="••••" />
			            </label>
			          </div>
			          	<div class="error" v-for="value in errors">{{ showError(value) }}</div>
			          	<button class="button submit-button" type="button" v-on:click="sendSms()">
			            	Продолжить
			          	</button>
			          	<p class="step__subtext">
			        	  	Вы можете <template v-if="timer > 0"><a href="javascript:;">запросить код повторно через 00:<template v-if="timer < 10">0</template>{{ timer }}</a></template><template v-else><a href="javascript:;" v-on:click="sendSmsAgain()">запросить код повторно</a></template>
			        	</p>
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
	import cookie from "vue-cookie"
	import checking from "../../mixins/checking"

	export default {
		mixins: [checking],
		data:() => ({
			loading: true,
			sms: "",
			phone: "",
			errors: [],
			timer: false,
			disabled: false,
			show: false,
			inteval: null
		}),
		mounted: function() {
			setTimeout(() => {
      		  	this.loading = false
      		}, 500)
      		setTimeout(() => {
      			let counter = cookie.get('counter')
      			if(counter > 0)
      		  		this.initTimer(counter)
      		  	else
      		  		this.initTimer()
      		}, 1000)
      		this.onSubmit()
		},
		methods: {
			sendSms() {
				this.errors = []
				if(this.sms.length == 0) {
					this.errors.push({sms: "Вы не ввели смскод"})
				} else if(this.sms.length > 0 && this.sms.length < 4 )
					this.errors.push({sms: "Вы ввели недостаточно цифр"})

				this.isInvalid('sms')
				if(this.errors.length == 0) {
					const data = new URLSearchParams()
					data.append('sms', this.sms)

					axios.get('/api/v1/change_phonesms', {params: data}).then(response => {
						console.log(response)
						if(response.data.status && response.data.data.hasOwnProperty('phone_changed')) {
							cookie.set('counter', '')
							this.$router.push({path: '/settings'})
								
						} else {
							if(response.data.data.hasOwnProperty('errors'))
								response.data.data.errors.forEach(($error) => {
									this.errors.push($error)
								})
						}
						this.isInvalid('sms')
					})
				}
			},
			sendSmsAgain() {
				this.errors = []
				if(this.disabled) return
				this.show = false
				this.loading = true

				const data = new URLSearchParams()
				data.append('action', 'phone')
				data.append('again', true)

				axios.get('/api/v1/change_phone', {params: data}).then(response => {
					console.log(response)
					if(response.data.status) {
						
					} else {
						if(response.data.data.hasOwnProperty('errors'))
							response.data.data.errors.forEach(($error) => {
								this.errors.push($error)
							})
					}
					this.initTimer()
					this.loading = false
				})
			},
			initTimer($timer = 60) {
				this.disabled = true
				this.show = true
				this.timer = $timer
				this.interval = setInterval(() => {
					if(this.timer <= 0) {
						this.disabled = false
						clearInterval(this.interval)
					} 
					this.timer -= 1
					cookie.set('counter', this.timer)
				}, 1000)
			}
		}
	}
</script>