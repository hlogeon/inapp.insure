export default {
	methods: {
		isMenuActive($href) {
			if( this.$route.path == $href ) 
				return {selected: true}
		}
	}
}