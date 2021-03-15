<template>
	<footer class="footer">
	  <!-- body -->
	  <div class="footer-body">
	    <div class="container">
	      <div class="footer-body-wrap">
	        <!-- left -->
	        <div class="footer-left">
	          <!-- logo -->
	          <router-link to="/" class="logo" title="inapp" style="display: none;">
	            <img src="/images/logo.svg" alt="inapp" class="logo__img" />
	          </router-link>
	          <!-- ./ End of logo -->
	
	          <div class="footer-phone-wrapper" v-if="phone">
	            <a :href="'tel:'+phone" class="footer-phone">
	              {{ phone }}
	            </a>
	
	            <p class="footer-phone-text">
	              Для звонков по всей России
	            </p>
	          </div>
	        </div>
	        <!-- ./ End of left -->
	
	        <!-- center -->
	        <div class="footer-center">
	          <nav class="footer-nav">
	            <div class="footer-nav-col">
	              <div class="footer-nav-col-wrap">
	                
	              </div>
	            </div>
	            <div class="footer-nav-col">
	              <BottomMenu :menus="bottomMenu1" />
	            </div>
	          </nav>
	        </div>
	        <!-- ./ End of center -->
	
	        <!-- right -->
	        <div class="footer-right">
	          <div class="footer-apps" style="display: none;">
	            <h5 class="footer__subtitle">
	              Приложение
	            </h5>
	
	            <div class="footer-apps-wrap">
	              <div class="header-apps">
	                <a href="#" class="button button-app">
	                  <img
	                    src="/images/appstore.svg"
	                    alt=""
	                    class="button-app__img"
	                  />
	                </a>
	                <a href="#" class="button button-app">
	                  <img
	                    src="/images/googleplay.svg"
	                    alt=""
	                    class="button-app__img"
	                  />
	                </a>
	              </div>
	            </div>
	          </div>
	
	          <div class="footer-social">
        		  <div class="footer-social-wrap">
        		    <a :href="telegram" class="footer-social-link footer-social-link--blue" v-if="telegram">
        		      <svg class="footer-social-link__icon">
        		        <use xlink:href="#telegram"></use>
        		      </svg>
        		    </a>
        		    <a :href="whatsapp" class="footer-social-link footer-social-link--green" v-if="whatsapp">
        		      <svg class="footer-social-link__icon">
        		        <use xlink:href="#whatsup"></use>
        		      </svg>
        		    </a>
        		    <a href="https://www.instagram.com/inapp.insure/" class="footer-social-link footer-social-link--pink" v-if="instagram">
        		      <svg class="footer-social-link__icon">
        		        <use xlink:href="#instagram"></use>
        		      </svg>
        		    </a>
        		    <a :href="facebook" class="footer-social-link footer-social-link--purple" v-if="facebook">
        		      <svg class="footer-social-link__icon">
        		        <use xlink:href="#facebook"></use>
        		      </svg>
        		    </a>
        		  </div>
        		</div>
	        </div>
	        <!-- ./ End of right -->
	      </div>
	    </div>
	  </div>
	  <!-- ./ End of body -->
	
	  <!-- bottom -->
	  <div class="footer-bottom">
	    <div class="container">
	      <div class="footer-bottom-wrap">
	        <div class="footer-bottom__text">
	          <p class="copyright" v-if="footer_information" v-html="textFormat(footer_information)">
	          </p>
	        </div>
	      </div>
	    </div>
	  </div>
	  <!-- ./ End of bottom -->
	</footer>
</template>

<script>
	import BottomMenu from "./auth/bottom_menu"

	export default {
		data:() => ({
			bottomMenu1: [],
			bottomMenu2: [],
			phone: "",
			footer_information: "",
			telegram: "",
			whatsapp: "",
			instagram: "",
			facebook: ""
		}),
		components: {
			BottomMenu
		},
		mounted: function () {
          	axios.get("/api/v1/bottom_menu").then(response => {
              if(response.data.status) {
              	console.log(response.data.data.bottom_menu_1)
              	let $data = response.data.data;
              	// $data.bottom_menu_1.forEach(($menus) => {
              	// 	this.bottomMenu1.push({
              	// 		name: $menus.name,
              	// 		href: $menus.href,
              	// 		id: $menus.id
              	// 	})
              	// })

              	this.bottomMenu1 = $data.bottom_menu_1
              	this.bottomMenu2 = $data.bottom_menu_2

              	// $data.bottom_menu_2.forEach(($menus) => {
              	// 	this.bottomMenu2.push({
              	// 		name: $menus.name,
              	// 		href: $menus.href,
              	// 		id: $menus.id
              	// 	})
              	// })

                this.separateInfo(response.data.data.info)
              }
            })
        },
        methods: {
        	separateInfo($info) {
        		$info.forEach(($element) => {
        			if($element.global_name == "phone")
        				this.phone = $element.value
        			else if($element.global_name == "footer_information")
        				this.footer_information = $element.value
        			else if($element.global_name == "telegram")
        				this.telegram = $element.value
        			else if($element.global_name == "whatsapp")
        				this.whatsapp = $element.value
        			else if($element.global_name == "instagram")
        				this.instagram = $element.value
        			else if($element.global_name == "facebook")
        				this.facebook = $element.value
        		})
        	},
        	textFormat($text) {
        		return ""+$text.replace(/\n/gi, "<br/>")
        	}
        }
	}
</script>