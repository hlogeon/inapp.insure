<template>
	<div class="cashback-all-accordion__content accordion-list">
		<div class="cashback-all-list">
			<label class="checkbox checkbox-cashback" v-for="cashback in cashbacks">
  				<input class="checkbox__input" type="checkbox" name="cashback" :value="cashback.id" v-on:click="checkCashback(cashback.id)" />
  				<div class="checkbox__custom">
  					<img
  					  	:src="'/storage/' + cashback.image"
  					  	:alt="cashback.name"
  					  	class="checkbox__bg"
  					/>
  					<div class="checkbox__icon-wrapper">
  						<svg class="checkbox__icon">
  						  	<use xlink:href="#done"></use>
  						</svg>
  					</div>
  				</div>
  			</label>
  		</div>
	</div>
</template>

<script>
	import accordion from "../../mixins/Accordion"
	export default {
		data:() => ({
			cashbacks: []
		}),
		mixins: [accordion],
		mounted() {
			axios.get('/api/v1/get_cashback').then(response => {
				if(response.data.status) {
					this.cashbacks = response.data.data
					setTimeout(()=>{
						this.initAccording()
					}, 600)
				}
			})
		},
		methods: {
			checkCashback($id) {
			  var ids = []

        document.querySelectorAll('.cashback-all-list input:checked').forEach($input => {
          ids.push($input.value);
        })

				this.$emit('chosenCashback', ids)
			}
		}
	}
</script>