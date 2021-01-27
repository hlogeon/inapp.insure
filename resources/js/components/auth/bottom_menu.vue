<template>
	<div class="footer-nav-col-wrap">
	 	<a :href="menu.href" v-bind:key="menu.id" class="footer-nav__link" v-for="menu in menus" target="_blank">
	 	   {{ menu.title }}
	 	</a>
	</div>
</template>

<script>
	export default {
		props: ['menus']
	}
</script>