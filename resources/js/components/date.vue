<template>
	<div>
		<input required class="input input--big" type="text" v-on:change="dateIs()" :placeholder="placeholder" />
	</div>
</template>

<script>
	export default {
		props: ["placeholder"],
		methods: {
			dateIs() {
				console.log(this)
			}
		}
	}
</script>