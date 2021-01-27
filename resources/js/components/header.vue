<template>
    <div>
      <header class="header">
          <div class="container">
              <!-- wrap -->
              <div class="header-wrap">
                  <!-- logo -->
                  <a href="https://inapp.insure"  class="logo" title="inapp">
                      <img src="/images/logo.svg" alt="inapp" class="logo__img" />
                      <img src="/images/logo-white.svg" alt="inapp" class="logo__img logo__img--on-mobile" />
                  </a>
                  <!-- ./ End of logo -->
                  <topmenu :menus="menuLists" />
                  <!-- controls -->
                  <div class="header-controls">
                    <login />
                  </div>
                  <!-- ./ End of controls -->
        
                  <!-- hamburger -->
                  <button class="hamburger j_toggleMenu">
                    <span class="hamburger__item"></span>
                    <span class="hamburger__item"></span>
                    <span class="hamburger__item"></span>
                  </button>
                  <!-- ./ End of hamburger -->
              </div>
              <!-- ./ End of wrap -->
          </div>
      </header>
      <!-- ./ End of header -->
      <div class="menu-overlay"></div>
      <!-- menu -->
      <div class="menu">
        <div class="container">
          <div class="menu-top" style="display: none;">
            <!-- logo -->
            <a href="#" class="logo" title="CRM GO:1.6">
              <img src="/images/logo.svg" alt="CRM GO:1.6" class="logo__img" />
            </a>
            <!-- ./ End of logo -->
      
            <!-- close -->
            <!-- <button class="menu-close__button j_closeMenu">
              <span class="menu-close__item"></span>
              <span class="menu-close__item"></span>
            </button> -->
            <!-- ./ End of close -->
          </div>
      
          <div class="menu-wrapper" data-scroll-lock-scrollable>
            <!-- wrap -->
            <div class="menu-wrap">
              <nav class="nav for-mobile" style="margin-bottom: 20px;">
                <router-link to="/cashback" class="nav-link">
                  Кэшбэк
                </router-link>
                <router-link to="/account" class="nav-link">
                  Мои полисы
                </router-link>
                <router-link to="/settings" class="nav-link">
                  Настройки
                </router-link>
              </nav>
              <topmenu :menus="menuLists" />
              <div class="footer-social">
                <div class="footer-social-wrap">
                  <a href="" class="footer-social-link footer-social-link--yellow">
                    <svg class="footer-social-link__icon">
                      <use xlink:href="#mail"></use>
                    </svg>
                  </a>
                  <a :href="telegram" class="footer-social-link footer-social-link--blue">
                    <svg class="footer-social-link__icon">
                      <use xlink:href="#telegram"></use>
                    </svg>
                  </a>
                  <a :href="whatsapp" class="footer-social-link footer-social-link--green">
                    <svg class="footer-social-link__icon">
                      <use xlink:href="#whatsup"></use>
                    </svg>
                  </a>
                  <a :href="instagram" class="footer-social-link footer-social-link--pink">
                    <svg class="footer-social-link__icon">
                      <use xlink:href="#instagram"></use>
                    </svg>
                  </a>
                  <a :href="facebook" class="footer-social-link footer-social-link--purple">
                    <svg class="footer-social-link__icon">
                      <use xlink:href="#facebook"></use>
                    </svg>
                  </a>
                </div>
              </div>

            </div>
            <!-- ./ End of wrap -->
          </div>
        </div>
        <!-- ./ End of container -->
      </div>
    </div>
</template>

<script>

    import login from "./auth/login"
    import topmenu from "./auth/menu"
  
    export default {
        components: {
            login,
            topmenu
        },
        data:() => ({
          menuLists: [],
          telegram: "",
          whatsapp: "",
          instagram: "",
          facebook: ""
        }),
        mounted: function () {
          this.getMenuLinks()
          try {
            let $header = document.querySelectorAll('.header')
            let $section = document.querySelectorAll('body')
            if($header.length > 0 && $section.length > 0) {
              $section[0].style.paddingTop = ($header[0].offsetHeight) + "px"
            }
            let $attr = "data-scroll-lock-scrollable"
            let $overlay = document.querySelector('.menu-overlay')
            let $menu = document.querySelector('.menu')
            document.querySelector('.j_toggleMenu').addEventListener('click', function() {
              document.querySelector('header.header').classList.toggle('active')
              $overlay.classList.toggle('active')
              $menu.classList.toggle('active')
              $menu.setAttribute($attr, true)
            })
            //document.querySelector('.j_toggleMenu').addEventListener('click', function() {
            //  $overlay.classList.remove('active')
            //  $menu.classList.remove('active')
            //  $menu.removeAttribute($attr)
            //})
          } catch(e) {}
        },
        methods: {
          getMenuLinks() {
            axios.get("/api/v1/top_menu").then(response => {
              if(response.data.status) {
                this.menuLists = []
                response.data.data.menu.forEach($menu => {
                  this.menuLists.push({
                    title: $menu.title,
                    href: $menu.href
                  })
                })
              }
            })
          }
        },
        separateInfo($info) {
            $info.forEach(($element) => {
              if($element.global_name == "telegram")
                this.telegram = $element.value
              else if($element.global_name == "whatsapp")
                this.whatsapp = $element.value
              else if($element.global_name == "instagram")
                this.instagram = $element.value
              else if($element.global_name == "facebook")
                this.facebook = $element.value
            })
          },
    }

</script>

<style scoped="">
  .profile__letter img {
    width: 18px;
    height: auto;
  }
</style>