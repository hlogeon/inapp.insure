<template>
	<label class="radio-pay" v-if="tarrif">
      	<input class="radio__input" type="radio" name="tar" :checked="id && id == tarrif.id" :id="'tar_'+tarrif.id" v-on:change="isTarrif(tarrif.id)" :value="tarrif.id" />
      	<div class="radio-pay__custom">
      	  <time class="radio-pay__time">
      	    {{ tarrif.per_month }} {{ GetNoun(tarrif.per_month, "месяц", "месяца", "месяцев" ) }}
      	  </time>
      	  <span class="radio-pay__title">
      	    {{ (tarrif.price_per) ? tarrif.price_per : Math.ceil( tarrif.price / tarrif.per_month ) }} ₽/месяц
      	  </span>
              <p class="radio-pay__text" v-if="tarrif.per_month > 1">
                Разовый платеж <em>{{ tarrif.price }} ₽</em>
              </p>
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
			},
      GetNoun(number, one, two, five) {
        number = Math.abs(number);
        number %= 100;
        if (number >= 5 && number <= 20) {
          return five;
        }
        number %= 10;
        if (number == 1) {
          return one;
        }
        if (number >= 2 && number <= 4) {
          return two;
        }
          return five;
      }
		}
	}
</script>