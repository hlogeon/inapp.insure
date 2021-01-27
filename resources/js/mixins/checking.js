export default {
	mounted() {
		axios.get('/api/v1/checking_phone').then(response => {
			if(response.data.status)
				this.phone = response.data.data
		})
	}
}