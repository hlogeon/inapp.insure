<template>
    <section class="polis-section">
        <div class="container">
            <div class="polis-info" style="margin-top: 0;">
                <h2 class="title">
                    Информация о полисе
                </h2>

                <div class="polis-info-wrap">
                    <div class="polis-info-inputs">
                        <!-- <div class="input-wrapper" v-bind:class="{invalid: current.status_id == 4}">
			  	      <label class="input-label">
			  	        <input
			  	          	required
			  	          	readonly
			  	          	class="input input--big"
			  	          	type="text"
			  	          	placeholder="Платежные данные"
			  	          	value="•••• •••• •••• 1942"
			  	          	v-bind:class="{invalid: current.status_id == 4}"
			  	        />
			  	        <div class="input__placeholder">
			  	          <span>Платежные данные</span>
			  	        </div>
			  	      </label>
			  	    </div> -->
                        <div class="input-wrapper" style="margin: 0;">
                            <label
                                class="input-label"
                                v-if="
                                    current.status_id == 4 ||
                                        current.subscribed == 0
                                "
                            >
                                <input
                                    required="required"
                                    type="tel"
                                    placeholder="Карта"
                                    readonly="readonly"
                                    class="input input--big input--transparent"
                                    value="Отключено"
                                />
                                <div class="input__placeholder">
                                    <span>Автопродление</span>
                                </div>
                            </label>
                            <div class="settings-table__row" v-else>
                                <div class="settings-table__col input-wrapper">
                                    <label class="input-label">
                                        <input
                                            required="required"
                                            type="tel"
                                            placeholder="Карта"
                                            readonly="readonly"
                                            class="input input--big input--transparent"
                                            :value="
                                                dateIs(new Date(current.finish))
                                            "
                                            style="padding-right: 0;"
                                        />
                                        <div class="input__placeholder">
                                            <span>Автопродление</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="polis-info-buttons">
                        <!-- <template v-if="current.status_id == 4">
			  	  		<button disabled class="button button--small">
			  	  		  	Страховой полис
			  	  		</button>
			  	  		<button disabled class="button button--small">
			  	  		  	Правила страхования
			  	  		</button>
			  	  		<button disabled class="button button--small">
			  	  		  	Информация о подписке
			  	  		</button>
			  	  	</template>
			  	    <template v-else>
			  	    	
			  	  		<a href="#" class="button button--small">
			  	  		  	Правила страхования
			  	  		</a>
			  	  		<a href="javascript:;" class="button button--small" @click="infoPage">
			  	  		  	Информация о подписке
			  	  		</a>
			  	    </template> -->
                        <a
                            :href="
                                '/get_pdf/' +
                                    this.current.id +
                                    '/Страховой%20полис.pdf'
                            "
                            target="_blank"
                            class="button button--small"
                            style="margin-left: 0;"
                        >
                            Страховой полис
                        </a>
                        <!-- <a href="javascript:;" class="button button--small" @click="addSecondPolic">
			  	      	<svg class="svg">
			  	      	  <use xlink:href="#plus"></use>
			  	      	</svg>
			  	      	Добавить новый полис
			  	    </a> -->
                        <a
                            href="javascript:;"
                            onclick="document.querySelector('.intercom-launcher').click()"
                            class="button button--small button--red"
                        >
                            Cтраховой случай
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import moment from "moment";

export default {
    data: () => ({
        pdf_href: false,
        errors: false,
        payment: false,
        src: "",
        b_text: "",
        disabled: false,
        api: "pk_19ba322d94cdaa881e611754cf8e3",
        user: false
    }),
    props: ["type", "current"],
    methods: {
        dateIs($date) {
            moment.lang("ru");
            return moment($date).format("DD MMMM YYYY") + " г.";
        },
        infoPage() {
            this.$router.push("/discribe/" + this.current.id);
        },
        pdfPage() {
            this.$router.push("/get_pdf/" + this.current.id);
        }

        // addSecondPolic() {
        //      		axios.get('/api/v1/add_aPolic').then(response => {
        //      		  	if(response.data.status && response.data.data) {
        //      		  	  this.$router.push('/authaddress')
        //      		  	}
        //      		})
        // }
    }
};
</script>
