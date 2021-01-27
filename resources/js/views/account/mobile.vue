<template>
	<div>
		<v-spinner v-if="loading" />
		<template v-else>
			<v-header></v-header>
		<section class="step-section">
 		 	<div class="container">
 		 	  <!-- wrap -->
 		 	  <div class="step-wrap">
			  
 		 	  	
 		 	    <h1 class="title">
 		 	    	<router-link to="/settings" class="button button-arrow button-arrow--prev">
           				<svg class="svg">
           				  <use xlink:href="#arrow"></use>
           				</svg>
           			</router-link>
 		 	      	Привет<template v-if="name">, {{ name }}</template>! Введи новый номер телефона
 		 	    </h1>

 		 	    <!-- form -->
 		 	    <div class="step-form">
 		 	    	
 		 	      	<div class="step-phone-wrapper">
 		 	      	  <div class="input-wrapper">
 		 	      	    <label class="input-label">
 		 	      	    	<input class="input phone input--big j_mask" type="tel" v-model="phone" v-bind:class="{ error: errors.length > 0 }" placeholder="Номер телефона" />
 		 	      	      <div class="input__placeholder">
 		 	      	        <span>Номер телефона</span>
 		 	      	      </div>
 		 	      	    </label>
 		 	      	  </div>
 		 	      	</div>
 		 	      	<div class="error" v-for="value in errors">{{ showError(value) }}</div>
 		 	     	<button class="button submit-button" type="button" :disabled="disabled" v-on:click="sendPhone()">
 		 	     	  	Продолжить
 		 	     	</button>
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
	import axios from "axios"
	import MaskedInput from 'vue-masked-input'
	import checking from "../../mixins/checking"

	export default {
		mixins: [checking],
		data:() => ({
			loading: true,
			phone: "",
			errors: [],
      disabled: false,
			name: ""
		}),
		mounted: function() {
			axios.get('/api/v1/get_personal').then(response => {
				if(response.data.status) {
					this.name = response.data.data.user.user_name
				}
			})
			setTimeout(() => {
      		  this.loading = false
      		}, 500)
      		setTimeout(() => {
      		  const inputs = document.querySelectorAll('.j_mask'); // Inputs

				if (inputs.length) {
				  // eslint-disable-next-line no-undef
				  const event = new Event('input');
				
				  inputs.forEach((input) => {
				    input.addEventListener('input', mask, false);
				    input.addEventListener('focus', mask, false);
				    input.addEventListener('blur', mask, false);
				    input.addEventListener('keydown', mask, false);
				
				    if (input.value !== '') {
				      input.dispatchEvent(event);
				      input.blur();
				    }
				  });
				}
				
				function setCursorPosition(pos, elem) {
				  elem.focus();
				  if (elem.setSelectionRange) elem.setSelectionRange(pos, pos);
				  else if (elem.createTextRange) {
				    const range = elem.createTextRange();
				    range.collapse(true);
				    range.moveEnd('character', pos);
				    range.moveStart('character', pos);
				    range.select();
				  }
				}
				
				function mask(event) {
				  if (this.selectionStart < 4) event.preventDefault();
				  const matrix = '+7 (___) ___-__-__';
				  let i = 0;
				  const def = matrix.replace(/\D/g, '');
				  let val = this.value.replace(/\D/g, '');
				
				  if (def.length >= val.length) val = def;
				  this.value = matrix.replace(/[_\d]/g, function(a) {
				    return i < val.length ? val.charAt(i++) : a;
				  });
				  i = this.value.indexOf('_');
				  if (event.keyCode === 8) i = this.value.lastIndexOf(val.substr(-1)) + 1;
				  if (i !== -1) {
				    i < 5 && (i = 4);
				    this.value = this.value.slice(0, i);
				  }
				  if (event.type === 'blur') {
				    if (this.value.length < 5) this.value = '';
				  } else setCursorPosition(this.value.length, this);
				}
      		}, 1000)
      		this.onSubmit()
		},
		methods: {
			sendPhone() {
				let regExp = /^\+7\s\(9[0-9\-\s\)]*$/
				this.errors = []
				if(!regExp.test(this.phone))
					this.errors.push({phone: "Некорректно введен телефон"})

				this.isInvalid('phone')

				if(this.errors.length == 0) {
					const data = new URLSearchParams()
					data.append('phone', this.phone)

          			this.disabled = true
					axios.get('/api/v1/change_phone', {params: data}).then(response => {
						console.log('change', data)
						if(response.data.status) {
							this.$router.push({ path: '/changesms' })
						} else {
							if(response.data.data.hasOwnProperty('errors'))
								response.data.data.errors.forEach(($error) => {

                  					this.disabled = false
									this.errors.push({phone: $error})
								})
						}
						this.isInvalid('phone')
					})
				}
			}
		}
	}
</script>