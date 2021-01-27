<template>
   <div>
      <v-spinner v-if="loading" />
      <main v-else>
         <v-header />
         <section class="subscribe-section">
            <div class="container">
               <!-- wrap -->
               <div class="subscribe-wrap">
                     <h1 class="title discribe-title">
                        Информация о подписке
                        <router-link to="/account" class="backto">
                           <svg width="85" height="85" viewBox="0 0 85 85" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M21.2147 21.211C23.0172 19.4086 25.8351 19.3043 27.5088 20.9779L63.8743 57.3434C65.5479 59.0171 65.4436 61.835 63.6411 63.6374C61.8387 65.4399 59.0208 65.5442 57.3471 63.8706L20.9816 27.5051C19.308 25.8314 19.4123 23.0135 21.2147 21.211Z" fill="#D0D0D0"/>
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M21.2128 63.6386C19.4104 61.8362 19.306 59.0182 20.9797 57.3445L57.3452 20.9791C59.0188 19.3054 61.8368 19.4097 63.6392 21.2122C65.4416 23.0146 65.546 25.8325 63.8723 27.5062L27.5068 63.8717C25.8331 65.5454 23.0152 65.441 21.2128 63.6386Z" fill="#D0D0D0"/>
                           </svg>
                        </router-link>
                     </h1>

                     <div class="error" v-if="error">{{ error }}</div>
         
                     <div class="subscribe-tariffs">
                         <div class="step-form-tariffs">
                              <div class="step-form-tariffs-wrap" v-if="tarrifs.length > 0">
                                <tarrif @tarrifIs="chosenTarrif" :id="tarrifId" v-bind:key="tarrif.id" v-for="tarrif in tarrifs" :tarrif="tarrif" />
                             </div>
                         </div>
                     </div>
         
                     <div class="subscribe-buttons">
                           <template v-if="tarrif && tarrifId == tarrif.id">
                                 <button type="button" disabled class="button">
                                      Изменить подписку 
                                 </button>
                           </template>
                           <template v-else>
                                 <a href="javascript:;" class="button" @click="changeTarrif()">
                                       Изменить подписку
                                 </a>
                           </template>
                           <a href="javascript:;" class="button button--red" @click="canselSubscribe()">
                                 Отменить подписку
                           </a>
                     </div>
         
                     <p class="subscribe__subtext" v-if="tarrif">
                         <template v-if="policy">
                               Следующее списание в размере {{ tarrif.amount }} ₽ произойдет {{ dateFormat(policy.finish) }}
                         </template>
                         <template v-else>
                              Следующее списание в размере {{ tarrif.amount }} ₽
                         </template>
                     </p>
               </div>
               <!-- ./ End of wrap -->
            </div>
            <!-- ./ End of container -->
         </section>
         <v-footer />
      </main>
   </div>
</template>

<script>

	import tarrif from "../../components/auth/tarrif"
      import moment from 'moment'

	export default {
		data:() => ({
         loading: true,
			tarrifs: [],
         policy: null,
         tarrif: null,
         tarrifId: null,
         success: "",
         error: ""
		}),
		components: {
			tarrif
		},
		mounted() {
         this.updatePage()
		},
      methods: {
         updatePage() {
               this.tarrifs = []
               const id = this.$route.params.id
               if( ! id ) this.toAccount()

               const data = new URLSearchParams()
               data.append('id', id)      

               axios.get('/api/v1/get_tarrifs', {params: data}).then(response => {
                     //console.log('tarrifs', response.data)
                     if(response.data.status && response.data.data.policy.subscribed) {
                           response.data.data.tarrifs.forEach(($tarrif) => {
                              console.log('sss', $tarrif)
                              this.tarrifs.push($tarrif)
                           })
                           let policy = response.data.data.policy
                           if(policy.status_id == 1 || policy.status_id == 2) {
                              this.policy = policy
console.log(policy.changed_tarrif)
                             if(policy.changed_tarrif>0) {
                               this.tarrifId = policy.changed_tarrif
                               this.chosenTarrif(policy.changed_tarrif)
                             }else{
                               this.tarrifId = policy.tarrif_id
                               this.chosenTarrif(policy.tarrif_id)
                             }
                           } else
                              this.toAccount()
                     } else
                           this.toAccount()
               })
         },
         toAccount() {
            this.$router.push('/account')
         },
         chosenTarrif($id) {
            this.tarrifs.forEach(($tarrif) => {
                  if($tarrif.id == $id) {
                        this.tarrif = $tarrif
                  }
            })
            setTimeout(() => {
                  this.loading = false
            }, 500)
         },
         changeTarrif() {
            this.error = ""
            this.success = ""

            const data = new URLSearchParams()
            data.append('tarrif_id', this.tarrif.id)
            data.append('police_id', this.$route.params.id)

            axios.get('/api/v1/set_tarrifs', {params: data}).then(response => {
                  if(response.data.status) {

                    this.tarrifId = this.tarrif.id
                    this.chosenTarrif(this.tarrif.id)
                     new StatusPopup({
                        text: "Ваш тариф был изменен, новые изменения вступят с " + response.data.data,
                        delay: 2000
                     })
                  } else
                        this.error = "Произошла ошибка во время изменения тарифа"
            })
         },
         dateFormat($date) {
               return moment(new Date($date)).format('DD.MM.YYYY')
         },
         canselSubscribe() {
               this.$router.push({
                     path: "/discribagreed/"+this.$route.params.id
               })
         }
      }
	}
</script>