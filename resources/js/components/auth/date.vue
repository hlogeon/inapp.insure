<template>
	<label class="radio-pay" v-if="tarrif">
      	<input class="radio__input" type="radio" name="tar" :checked="id && id == tarrif.id" :id="'tar_'+tarrif.id" v-on:change="isTarrif(tarrif.id)" :value="tarrif.id" />
      	<div class="radio-pay__custom">
      	  <time class="radio-pay__time">
      	    {{ tarrif.per_month }} месяц<template v-if="tarrif.per_month > 1">а</template>
      	  </time>
      	  <span class="radio-pay__title">
      	    {{ tarrif.price * tarrif.per_month }} ₽/месяц
      	  </span>
      	</div>
    </label>
</template>

<script>
	export default {
		props: {
                  tarrif: false,
                  id: false
            },
		methods: {
			isTarrif($id) {
				this.$emit('tarrifIs', $id)
			}
		}
	}
</script>