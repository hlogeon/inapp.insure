<template>
	<div>
		<v-spinner v-if="loading" />
		<template v-else>
			<v-header></v-header>
			<section class="step-section">
			    <div class="container">
			      <!-- wrap -->
			      <div class="step-wrap">
			        <ul class="steps-list for-desktop-inline-flex" v-if="prev">
					  <li class="steps-list-item steps-list-item--completed">
					    <i class="steps-list-item__num"></i>
					  </li>
					  <li class="steps-list-item steps-list-item--current">
					    <i class="steps-list-item__num"></i>
					  </li>
					  <li class="steps-list-item">
					    <i class="steps-list-item__num"></i>
					  </li>
					</ul>
					<ul class="steps-list for-mobile-inline-flex" v-if="prev">
					  <li class="steps-list-item steps-list-item--completed">
					    <i class="steps-list-item__num"></i>
					  </li>
					  <li class="steps-list-item steps-list-item--completed">
					    <i class="steps-list-item__num"></i>
					  </li>
					  <li class="steps-list-item steps-list-item--current">
					    <i class="steps-list-item__num"></i>
					  </li>
					</ul>

			        <h1 class="title">
			        	<a @click="previousPage" class="button button-arrow button-arrow--prev">
           				  <svg class="svg">
           				    <use xlink:href="#arrow"></use>
           				  </svg>
           				</a>
						<span class="for-desktop">
            			  Введи код из SMS
            			</span>

            			<span class="for-mobile">
            			  Введи проверочный <br />
            			  код из SMS
            			</span>
			        </h1>

			        <p class="step__text for-desktop">
			          Мы отправили проверочный код на номер {{ phone }}
			        </p>

			        <!-- form -->
			        <div class="step-form">
			        	
			         	<div class="input-wrapper input-wrapper--code">
			         	  <label class="input-label">
			         	    <input required class="input sms" v-model="sms" type="text" maxlength="4" placeholder="••••" />
			         	  </label>
			         	</div>
			           	<div class="error" v-for="value in errors">{{ showError(value) }}</div>
			         	<button class="button submit-button" type="button" v-on:click="sendSmsAuth()">
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

	export default {
		data:() => ({
			loading: true,
			sms: "",
			phone: "",
			errors: [],
			nextStep: "",
			timer: false,
			disabled: false,
			show: false,
			inteval: null,
			prev: false
		}),
		mounted: function() {
			if(this.$route.query.hasOwnProperty('redirect') && this.$route.query.redirect == "/authpayment")
				this.prev = true
			let data = new URLSearchParams()
       		data.append('action', 'checking')
       		axios.get('/api/v1/send_phone', {params: data}).then(response => {
       		  	if(response.data.data.hasOwnProperty('phone') && response.data.data.phone) {
       		  	  	this.phone = response.data.data.phone
       		  	}
       		})
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
			previousPage() {
      		  	return this.$router.push('/authphone')
      		},
			sendSmsAuth() {
				this.errors = []
				if(this.sms.length == 0) {
					this.errors.push({sms: "Вы не ввели смскод"})
				} else if(this.sms.length > 0 && this.sms.length < 4 )
					this.errors.push({sms: "Вы ввели недостаточно цифр"})

				this.isInvalid('sms')
				if(this.errors.length == 0) {
					const data = new URLSearchParams()
					data.append('sms', this.sms)
					data.append('action', 'sms')

					axios.get('/api/v1/send_phone', {params: data}).then(response => {
						console.log(response)
						if(response.data.data.hasOwnProperty('phone') && response.data.data.phone) {
							this.phone = response.data.data.phone
						}
						if(response.data.status && response.data.data.hasOwnProperty('register_confirm') && response.data.data.register_confirm) {
							if(response.data.data.hasOwnProperty('nextStep') && response.data.data.nextStep.length > 0)
								this.$router.push({
									path: response.data.data.nextStep, 
									query: {
										previously: this.$route.query.redirect
									}
								})
							else {
								if(this.$route.query.hasOwnProperty('redirect')) {
									let $url = (this.$route.query.redirect != "") ? this.$route.query.redirect : "/"
									this.$router.push({
										path: $url, 
										query: { previously: this.$route.query.redirect }
									})
								} else	
									this.$router.push({ 
										path: '/authpayment', 
										query: { 
											previously: this.$route.query.redirect 
										}
									})
							}
								
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

				axios.get('/api/v1/send_phone', {params: data}).then(response => {
					console.log(response)
					if(response.data.status) {
						if(response.data.data.phone) {
							
						}
					} else {
						this.initTimer()
						if(response.data.data.hasOwnProperty('errors'))
							response.data.data.errors.forEach(($error) => {
								this.errors.push($error)
							})
					}
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

<style scoped="">
	.for-mobile-inline-flex li:nth-child(1) {
		flex: 1 1 25% !important;
	}
</style>