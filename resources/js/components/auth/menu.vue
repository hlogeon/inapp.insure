<template>
    <nav class="nav">
     	<router-link :to="menu.href" v-bind:key="menu.href" v-bind:class="isMenuActive(menu.href)" class="nav-link" v-for="menu in menus">
     	  	{{ menu.title }}
     	</router-link>
     	<a href="/account" class="nav-link for-mobile" v-if="!auth">
     		Вход
     	</a>
     	<a href="javascript:;" class="nav-link for-mobile" @click="logout" v-else>
     		Выйти
     	</a>
    </nav>
</template>

<script>
	import mixin from "../../mixins/menu";
	export default {
		props: ['menus'],
		mixins: [mixin],
		data:() => ({
			auth: false
		}),
		mounted() {
			axios.get("/api/v1/get_user_data").then(response => {
				//console.log('res', response)
				if(response.data.status) {
					this.auth = true
				}
			})
		},
		methods: {
			logout() {
				if(this.auth) {
					axios.get("/api/v1/logout").then(response => {
						this.auth = false
						this.$router.push('/')
					})
				}
			},
		}
	}
</script>