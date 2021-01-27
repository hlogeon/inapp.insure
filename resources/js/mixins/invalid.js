export default {
	methods: {
		onSubmit(){
			document.addEventListener('keypress', (e) => {
				if (e.key === 'Enter') {
    			  	const submit = document.querySelector('.submit-button')
    			  	try {
    			  		submit.click()
    			  	} catch(e){}
    			}
			})
		},
		isInvalid($elem) {
			let $done = false
			if(this.errors) {
				this.errors.forEach(($currentValue) => {
					for(let $index in $currentValue) {
						if($index == $elem) {
							let $parent = this.findParentElement($elem)
							if($parent) {
								let $class = $parent.classList
								$class.remove('valid')
								$class.add('invalid')
								$done = true
							}
						}
					}
				})
			}
			
			if( ! $done ) {
				let $parent = this.findParentElement($elem)
				if($parent) {
					let $class = $parent.classList
					$class.remove('invalid')
					$class.add('valid')
				}
			}
      	},

      	findParentElement($elem) {
      		let $element = document.querySelector("."+$elem)
			if($element) {
				let $parent = this.parents($element, '.input-wrapper')
				if($parent)
					return $parent
			}
			return false
      	},
      	parents (el, sel) {
		    while ((el = el.parentElement) && !((el.matches || el.matchesSelector).call(el,sel)));
		    return el;
		},
		showError($value) {
			console.log('check', $value);
			let value = ""
			if($value) {
				for(var propName in $value) {
				    if($value.hasOwnProperty(propName)) {
				        value = $value[propName];
				        // do something with each element here
				    }
				}
			}
			return value
		}
	}
}