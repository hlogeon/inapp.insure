export default {
  data:() => ({
    popup: false
  }),
  mounted() {
    setTimeout(() => {
      axios.get('/api/v1/check_message').then(response => {
        if(response.data != "") {
          new StatusPopup({
              text: response.data,
              delay: 2000
          })
        }
      }).catch(e => {
          console.log(e)
      })
    }, 1000)
  }
}