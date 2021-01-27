<template>
	<div class="settings-personal">
  	  <h3 class="block-title">Личные данные</h3>
  	  <div class="settings-personal__wrapper">
  	    <div class="settings-wrapper__group">
  	      <div class="settings-wrapper__item input-wrapper">
  	        <label class="input-label">
  	          <input required class="input input--big" type="text" v-model="user_name" placeholder="Имя" />
  	          <div class="input__placeholder"><span>Имя</span></div>
  	        </label>
  	      </div>
  	      <div class="settings-wrapper__item input-wrapper">
  	        <label class="input-label">
  	          <input required class="input input--big" type="text" v-model="user_surname" placeholder="Фамилия" />
  	          <div class="input__placeholder"><span>Фамилия</span></div>
  	        </label>
  	      </div>
  	    </div>
  	    <div class="settings-wrapper__group">
  	      <div class="settings-wrapper__item input-wrapper">
  	        <label class="input-label">
  	          	<DatePicker class="input input--big" placeholder="Дата активации полиса" :formatter="customFormatter" v-model="user_birsday" />
  	          	<div class="input__placeholder"><span>Дата рождения</span></div>
  	        </label>
  	      </div>
  	    </div>
  	  </div>
  	</div>
</template>

<script>
	import DatePicker from 'vue2-datepicker'
  	import 'vue2-datepicker/index.css'
  	import 'vue2-datepicker/locale/ru'
  	import moment from 'moment'
  	import date from "../../mixins/date"

	export default {
		data: () => ({
			user_name: "",
       		user_surname: "",
       		user_birsday: "",
			customFormatter: {
        	  	stringify: (date) => {
        	  	    return (date) ? moment(date).format('DD.MM.YYYY') : '';
        	  	}
        	}
		}),
		mixins: [date],
    	components: {
    		 DatePicker
    	},
		mounted: function() {
			axios.get('/api/v1/get_personal').then(response => {
         		setTimeout(() => {
            	  	this.loading = false
            	}, 500)
         		if(response.data.status) {
         		 	 console.log(response.data)  
         		}
         	}).catch(e => {
         	  	console.log(e)
         	})
		}
	}
</script>