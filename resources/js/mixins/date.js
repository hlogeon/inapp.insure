export default {
	mounted: function() {
		document.querySelectorAll('.mx-input').forEach(($elem) => {
      	  	$elem.classList.add('input')
      	  	$elem.classList.add('input--big')
      	  	$elem.addEventListener('focus', function(){
      	  	  this.parentNode.parentNode.parentNode.classList.add('focus')
      	  	})
      	  	$elem.addEventListener('blur', function(){
      	  	  this.parentNode.parentNode.parentNode.classList.remove('focus')
      	  	})
      	})
	}
}