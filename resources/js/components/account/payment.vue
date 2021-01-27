<template>
	<div>
		<div class="settings-table__row" :key="payment.id" v-for="payment in payments">
  	  <a href="javascript:;" class="settings-table__row-link" @click="openCheck(payment.link)">
  	    <svg class="settings-table__row-icon">
  	      <use xlink:href="#download"></use>
  	    </svg>
  	  </a>
  	  <div class="settings-table__col settings-past-payments__adress input-wrapper">
  	    <label class="input-label">
  	      <input
  	        required
  	        class="input input--big input--transparent"
  	        type="text"
  	        :value="payment.address"
  	        readonly
  	      />
  	    </label>
  	  </div>
  	  <div class="settings-table__col settings-past-payments__data input-wrapper">
  	    <label class="input-label">
  	      <input
  	        required
  	        class="input input--big input--transparent"
  	        type="text"
  	        :value="payment.period"
  	        readonly
  	      />
  	    </label>
  	  </div>
  	  <div class="settings-table__col settings-past-payments__total input-wrapper">
  	    <label class="input-label">
  	      <input
  	        required
  	        class="input input--big input--bold input--transparent"
  	        type="text"
  	        :value="payment.price"
  	        readonly
  	      />
  	    </label>
  	  </div>
  	</div>
	</div>
</template>

<script>
	export default {
		props: ["payments"],
    methods: {
      openCheck($link) {
        window.open($link)
      }
    }
	}
</script>