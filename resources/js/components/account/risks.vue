<template>
	<section class="polis-section polis-section--risk" style="margin-top: 50px">
        <div class="container">
          <!-- wrap -->
          	<div class="polis-wrap">
            	<div class="polis-content">
					<div class="polis-risk">
					 	<h2 class="title">
					 	  	Страхуемые риски
					 	</h2>

					 	<ul class="polis-risk-list">
					 	  	<li class="polis-risk-item" v-for="risk in risks">
					 	  	  	{{ risk.name }}
					 	  	</li>
					 	</ul>
					</div>
				</div>
            	<div class="polis__img-wrapper">
            	  	<img src="/images/polis_risk.svg" alt="" class="polis__img" />
            	</div>
          	</div>
          	<!-- ./ End of wrap -->
        </div>
        <!-- ./ End of container -->
    </section>
</template>

<script>
	export default {
		props: ["type"],
		data:() => ({
			risks: []
		}),
		mounted() {
			axios.get('/api/v1/get_risks').then(response => {
				if(response.data.status) {
					this.risks = response.data.data
				}
			})
		}
	}
</script>