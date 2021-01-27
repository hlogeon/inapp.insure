<template>
	<div>
		<v-spinner v-if="loading" />
		<template v-else>
			<v-header></v-header>
			<section class="step-section">
				<div class="container">
					<div class="row">
						<router-link to="/account"  class="button">
    			 		  	Войти в личный кабинет
    			 		</router-link>
						<router-link to="/authaddress"  class="button">
    			 		  	Создать полис
    			 		</router-link>
    			 		<router-link to="/settings"  class="button">
    			 		  	Страница настроек
    			 		</router-link>
					</div>
				</div>
			</section>
		</template>
	</div>
</template>

<script>
	export default {
		data:() => ({
			loading: true
		}),
		mounted: function() {
      this.$router.push('/account')
			setTimeout(() => {
      		  	this.loading = false
      		}, 500)
      		setTimeout(() => {
      			document.body.click()
      		}, 1000)
		}
	}
</script>