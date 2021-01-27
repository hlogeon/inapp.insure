<template>
	<div>
		<v-spinner v-if="loading" />
		<template v-else>
			<v-header />
			<main>
			  <!-- cashback -->
			  <section class="cashback-section">
			    <div class="container">
			      <!-- wrapper -->
			      <div class="cashback-wrapper">
			        <!-- content -->
			        <div class="cashback-content">
			          <h1 class="title">Кэшбэк</h1>
			          <!-- info -->
			          <div class="cashback-info" v-if="user && user.cashback > 0">
			            У тебя {{ user.cashback }} кэшбэк баллов, ты сможешь их потратить в приложении, когда оно станет доступно
			          </div>
			          <div class="cashback-info" v-else>
			          	У тебя пока нет кэшбэк баллов!
			          </div>
			          <!-- ./ End of info -->
			          <!-- list -->
			          <div class="cashback-list">
			            <h3 class="cashback-list__title" v-if="user">
			              Вот небольшой список сервисов, где они тебе пригодятся:
			            </h3>

			            <Cashback @userIs="userIs" />

			          </div>
			          <!-- ./ End of list -->
			        </div>
			        <!-- ./ End of content -->
			        <!-- image -->
			        <div class="cashback-image">
			          <img src="/images/cashback_img1.svg" />
			        </div>
			        <!-- ./ End of image -->
			      </div>
			      <!-- ./ End of wrapper -->
			    </div>
			    <!-- ./ End of container -->
			  </section>
			  <!-- ./ End of cashback -->
			</main>
			<v-footer />
		</template>
	</div>
</template>

<script>
	import Cashback from "../../components/pages/cashbackpublic"

	export default {
		data:() => ({
			loading: true,
			user: null
		}),
		components: {
			Cashback
		},
		mounted: function() {
			setTimeout(() => {
      		  	this.loading = false
      		}, 500)
      		setTimeout(() => {
      			document.body.click()
      		}, 1000)
		},
		methods: {
			userIs($user) {
				if($user)
					this.user = $user
			}
		}
	}
</script>