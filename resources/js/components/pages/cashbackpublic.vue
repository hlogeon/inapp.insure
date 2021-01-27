<template>
	<div class="cashback-list__wrapper">
	  	<div class="cashback-item" v-for="cashback in cashbacks">
	  	  <a href="javascript:;" class="cashback-item__link">
	  	    <div class="cashback-item__image">
	  	      <img 
	  	      	:src="'/storage/' + cashback.image"
	  	      	:alt="cashback.name"
	  	      />
	  	    </div>
	  	  </a>
	  	</div>
	</div>
</template>

<script>
	export default {
		data:() => ({
			cashbacks: []
		}),
		mounted() {
			axios.get('/api/v1/get_cashback_public').then(response => {
				if(response.data.status) {
					this.cashbacks = response.data.data
					if(response.data.user)
						this.$emit('userIs', response.data.user)
				}
			})
		}
	}
</script>