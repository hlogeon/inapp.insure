class Auth {
    constructor(axios) {
    	this.axios = axios
    }

    check() {
    	return this.axios.get('/api/v1/get_user')
    }
}

export default Auth;