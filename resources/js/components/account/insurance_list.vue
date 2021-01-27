<template>
	<div>
		<div class="polis-house" v-if="type" style="margin-top: 0;">
		  <h2 class="title" v-if="!current">
		    Квартира будет застрахована
		  </h2>
		  <h2 v-else>
		  	Квартира застрахована
		  </h2>

		  <ul class="polis-house-list">
		    <li class="polis-house-item" v-for="insurance in insurances">
		      <div data-accordion class="polis-house-accordion">
		        <div class="polis-house-accordion__header">
		          <div class="polis-house-accordion__left">
		            <span class="polis-house-accordion__title">
		              {{ insurance.name }}
		            </span>
		            <em class="polis-house-accordion__price">
		              {{ insurance.price }} ₽
		            </em>
		          </div>

		          <button
		            class="button button-icon accordion-button"
		            type="button"
		          >
		            <svg class="svg">
		              <use xlink:href="#plus"></use>
		            </svg>
		          </button>
		        </div>

		        <div
		          class="polis-house-accordion__content accordion-list"
		        >
		          <div class="polis-house-accordion__wrap">
		            <p class="polis-house-accordion__text">
		            	{{ insurance.text }}
		            </p>
		          </div>
		        </div>
		      </div>
		    </li>
		  </ul>
		</div>


		<div class="polis-house" v-else>
          <h2 class="title">
            Квартира не застрахована
          </h2>

          <ul class="polis-house-list">

            <li class="polis-house-item" v-for="insurance in insurances">
              <div data-accordion class="polis-house-accordion">
                <div class="polis-house-accordion__header">
                  <div class="polis-house-accordion__left">
                    <span class="polis-house-accordion__title">
                      {{ insurance.name }}
                    </span>
                    <em class="polis-house-accordion__price">
                      {{ insurance.price }} ₽
                    </em>
                  </div>

                  <button class="button button-icon button-icon--red accordion-button" type="button">
                    <svg class="svg">
                      <use xlink:href="#plus"></use>
                    </svg>
                  </button>
                </div>

                <div
		          class="polis-house-accordion__content accordion-list"
		        >
		          <div class="polis-house-accordion__wrap">
		            <p class="polis-house-accordion__text">
		            	{{ insurance.text }}
		            </p>
		          </div>
		        </div>
              </div>
            </li>

          </ul>
        </div>
	</div>
</template>

<script>
	import according from "../../mixins/Accordion"

	export default {
		props: ["type", "current"],
		data: () => ({
			insurances: []
		}),
		mixins: [according],
		mounted() {
			
      		axios.get('/api/v1/get_insurances').then(response => {
				if(response.data.status) {
					this.insurances = response.data.data
					setTimeout(() => {
						this.initAccording()
					}, 500)
				}
			})
		}
	}
</script>

<style scoped="">
	.polis-house-accordion__text {
		line-height: 1.5;
	}
</style>