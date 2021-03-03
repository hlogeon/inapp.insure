<template>
    <div>
        <div class="error" v-if="errors">{{ errors }}</div>
        <div class="step-form-card-wrap">
            <button
                class="button submit-button"
                :disabled="disabled"
                type="button"
                @click="toPay()"
            >
                <span>{{ b_text ? b_text : "Оплатить" }}</span>

                <template v-if="price > 0">
                    <span class="price"
                        >{{ price.toLocaleString("ru-Ru") }} ₽</span
                    >
                    <img svg-inline src="./assets/arrow.svg" />
                </template>
            </button>
        </div>
    </div>
</template>

<script>
//:width="width" :height="height"
export default {
    props: {
        period: "",
        price: 0,
        method: "",
        tarrif_id: null,
        user_id: null,
        hidden: false,
        modal: true,
        title: "",
        width: 320,
        height: 200,
        success: false,
        interval: null,
        btn_text: "",
        tarrif: false
    },
    data: () => ({
        pay: false,
        errors: false,
        payment: false,
        src: "",
        b_text: "",
        disabled: false,
        api: "pk_19ba322d94cdaa881e611754cf8e3",
        user: false
    }),
    components: {
        //SweetModal
    },
    mounted() {
        console.log("user_id", this.user_id);
        let cloudPayment = document.createElement("script");
        cloudPayment.setAttribute(
            "src",
            "https://widget.cloudpayments.ru/bundles/cloudpayments"
        );
        document.head.appendChild(cloudPayment);
        this.b_text = this.btn_text;
        //this.updateData()
        this.onSubmit();
    },
    methods: {
        checkingPayment(timer = 5000) {
            this.interval = setInterval(() => {
                this.updateData().then(response => {
                    console.log("checking!!!", response);
                    if (
                        response.data.data.hasOwnProperty("Status") &&
                        response.data.data.Status != null
                    ) {
                        if (response.data.data.Status == "Completed") {
                            this.$refs.tinkoff.close();
                            this.$emit("isPaying", true);
                            this.disabled = false;
                        } else {
                            this.errors =
                                "Оплата не прошла, пожалуйста повторите попытку позже";
                            //this.$emit('isPaying', false)
                        }
                        try {
                            clearInterval(this.interval);
                        } catch (e) {}
                    }
                });
            }, timer);
        },
        updateData() {
            const data = new URLSearchParams();

            data.append("tarrif_id", this.tarrif.id);
            data.append("period", this.period);
            data.append("user_id", this.user_id);
            if (this.method == "refund") {
                data.append("refund", 1);
            }

            return axios.get("/api/v1/what_is_going_on", { params: data });
        },
        toPay() {
            this.disabled = true;
            //this.$emit('isPaying', this.data)
            this.updateData().then(response => {
                if (
                    response.data.data.hasOwnProperty("payed") &&
                    response.data.data.payed == 1
                ) {
                    this.$emit("isPaying", true);
                } else if (
                    this.method == "refund" ||
                    (response.data.data.hasOwnProperty("payment") &&
                        response.data.data.payment.hasOwnProperty("Status") &&
                        response.data.data.payment.Status != "Completed")
                ) {
                    this.payment = response.data.data.payment;
                    this.user = response.data.data.user;
                    this.charge();
                } else if (
                    response.data.data.hasOwnProperty("payment") &&
                    response.data.data.payment.Status == "Completed"
                ) {
                    this.$emit("isPaying", true);
                } else {
                    if (response.data.data.hasOwnProperty("errors"))
                        this.errors = response.data.data.errors;
                    this.disabled = false;
                }
            });
        },
        init() {
            this.disabled = true;
            let $this = this;
            var widget = new cp.CloudPayments();
            widget.pay(
                "auth", // или 'charge'
                {
                    //options
                    publicId: this.api, //id из личного кабинета
                    description: this.title, //назначение
                    amount: 100, //сумма
                    currency: "RUB", //валюта
                    invoiceId: "1234567", //номер заказа  (необязательно)
                    //accountId: 'user@example.com', //идентификатор плательщика (необязательно)
                    skin: "mini" //дизайн виджета (необязательно)
                    //data: {
                    //    myProp: 'myProp value'
                    //}
                },
                {
                    onSuccess: function(options) {
                        // success
                        console.log("success", options);
                        $this.$emit("isPaying", true);
                    },
                    onFail: function(reason, options) {
                        // fail
                        console.log("fail", reason, options);
                        $this.$emit("isPaying", false);
                    },
                    onComplete: function(paymentResult, options) {
                        //Вызывается как только виджет получает от api.cloudpayments ответ с результатом транзакции.
                        console.log("complete", paymentResult, options);
                        //this.$emit('isPaying', true)
                    }
                }
            );
        },
        charge() {
            var widget = new cp.CloudPayments();
            if (
                (this.user && this.tarrif && this.payment) ||
                (this.user && this.payment && this.method == "refund")
            ) {
                let floatPrice =
                    this.method != "refund"
                        ? this.tarrif.price.toFixed(2)
                        : this.price.toFixed(2);
                let price =
                    this.method != "refund" ? this.tarrif.price : this.price;

                var data = {
                    cloudPayments: {
                        CustomerReceipt: {
                            Items: [
                                //товарные позиции
                                {
                                    label: this.title
                                        ? this.title
                                        : "Подписка на страховку", //наименование товара
                                    price: +floatPrice, //цена
                                    quantity: 1.0, //количество
                                    amount: +floatPrice, //сумма
                                    vat: 0 //ставка НДС
                                }
                            ],
                            calculationPlace: "my.inapp.insure", //место осуществления расчёта, по умолчанию берется значение из кассы
                            taxationSystem: 2, //система налогообложения; необязательный, если у вас одна система налогообложения
                            //"email": "email@example.ru", //e-mail покупателя, если нужно отправить письмо с чеком
                            phone: this.user.user_phone, //телефон покупателя в любом формате, если нужно отправить сообщение со ссылкой на чек
                            isBso: false, //чек является бланком строгой отчётности
                            AgentSign: null, //признак агента, тег ОФД 1057
                            amounts: {
                                electronic: +floatPrice // Сумма оплаты электронными деньгами
                            }
                        }, //онлайн-чек
                        recurrent: { interval: "Month", period: 1 }
                    }
                };

                //console.log(data);
                // console.log({ // options
                //   publicId: this.api, //id из личного кабинета
                //   description: (this.tarrif) ? this.tarrif.name : this.title, //назначение
                //   amount: this.tarrif.price, //сумма
                //   currency: 'RUB', //валюта
                //   skin: "mini", //дизайн виджета
                //   invoiceId: this.payment,
                //   accountId: 'user' + this.user.id + '@client.com', //идентификатор плательщика (обязательно для создания подписки)
                // });
                let $this = this;
                widget.pay(
                    "charge",
                    {
                        // options
                        publicId: this.api, //id из личного кабинета
                        description: this.tarrif
                            ? this.tarrif.name
                            : this.title, //назначение
                        amount: price, //сумма
                        currency: "RUB", //валюта
                        skin: "mini", //дизайн виджета
                        invoiceId: this.payment.id,
                        accountId: "user" + this.user.id + "@client.com", //идентификатор плательщика (обязательно для создания подписки)
                        data: data
                    },
                    {
                        onSuccess: function(options) {
                            // success
                            console.log("Успех");
                            $this.$emit("isPaying", true);
                        },
                        onFail: function(reason, options) {
                            // fail

                            console.log("Неуспех");
                            $this.disabled = false;
                        },
                        onComplete: function(paymentResult, options) {}
                    }
                );
            }
        },
        clearData() {
            this.errors = "";
            this.src = "";
        }
    }
};
</script>

<style lang="scss" scoped>
.button {
    background: linear-gradient(109.61deg, #2ec86b 2.51%, #b3d491 91.16%);
    border-radius: 24px;
    display: flex;
    padding: 0;
    font-family: SFProRounded;
    font-style: normal;
    font-weight: bold;
    font-size: 28px;
    align-items: center;
    width: 490px;
    height: 70px;
    padding: 0 30px;
    justify-content: space-between;
    .price {
        margin-left: auto;
        margin-right: 10px;
    }
}
</style>
