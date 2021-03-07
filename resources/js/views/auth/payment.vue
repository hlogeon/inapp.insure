<template>
    <div>
        <v-spinner v-if="loading" />
        <template v-else>
            <v-header></v-header>
            <section class="step-section">
                <div class="container">
                    <!-- wrap -->
                    <div class="step-wrap">
                        <ul
                            class="steps-list for-desktop-inline-flex"
                            v-if="!another"
                        >
                            <li
                                class="steps-list-item steps-list-item--completed"
                            >
                                <i class="steps-list-item__num"></i>
                            </li>
                            <li
                                class="steps-list-item steps-list-item--current"
                            >
                                <i class="steps-list-item__num"></i>
                            </li>
                            <li class="steps-list-item">
                                <i class="steps-list-item__num"></i>
                            </li>
                        </ul>
                        <ul
                            class="steps-list for-mobile-inline-flex"
                            v-if="!another"
                        >
                            <li
                                class="steps-list-item steps-list-item--completed"
                            >
                                <i class="steps-list-item__num"></i>
                            </li>
                            <li
                                class="steps-list-item steps-list-item--completed"
                            >
                                <i class="steps-list-item__num"></i>
                            </li>
                            <li
                                class="steps-list-item steps-list-item--completed"
                            >
                                <i class="steps-list-item__num"></i>
                            </li>
                        </ul>

                        <h1 class="title">
                            <!-- <a
                                @click="previousPage"
                                class="button button-arrow button-arrow--prev"
                                v-if="!another"
                            >
                                <svg class="svg">
                                    <use xlink:href="#arrow"></use>
                                </svg>
                            </a>
                            <router-link
                                to="/authaddress"
                                class="button button-arrow button-arrow--prev"
                                v-else
                            >
                            </router-link> -->
                            <template v-if="!another">
                                Выберите тариф и период подписки
                            </template>
                            <template v-else>
                                Выберите тариф и укажите дату<br />активации
                                полиса
                            </template>
                        </h1>

                        <Paymenttype
                            @changePeriod="changePeriodHandler"
                            v-bind:period="period"
                        />

                        <!-- form -->
                        <div class="step-form">
                            <div class="step-form-tariffs">
                                <div
                                    class="step-form-tariffs-wrap"
                                    v-if="tarrifs.length > 0"
                                >
                                    <div class="plans_flex">
                                        <div class="tarif_block">
                                            <tarrif
                                                @tarrifIs="chosenTarrif"
                                                :id="tarrif_id"
                                                :period="period"
                                                v-bind:key="tarrif.id"
                                                v-for="tarrif in tarrifs"
                                                :tarrif="tarrif"
                                            />
                                        </div>
                                        <div class="desc_block">
                                            <description :id="tarrif_id" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="step-form-data">
                                <ul class="step-form-data-list">
                                    <li class="step-form-data-item">
                                        <span
                                            class="step-form-data-item__subtitle"
                                        >
                                            Город, улица, номер дома
                                        </span>
                                        <span
                                            class="step-form-data-item__value"
                                        >
                                            {{ address }}
                                        </span>
                                    </li>
                                    <li class="step-form-data-item">
                                        <span
                                            class="step-form-data-item__subtitle"
                                        >
                                            Номер квартиры
                                        </span>
                                        <span
                                            class="step-form-data-item__value"
                                        >
                                            {{ appartment }}
                                        </span>
                                    </li>
                                    <li class="step-form-data-item">
                                        <span
                                            class="step-form-data-item__subtitle"
                                        >
                                            Номер телефона
                                        </span>
                                        <span
                                            class="step-form-data-item__value"
                                        >
                                            {{ phone }}
                                        </span>
                                    </li>
                                </ul>
                                <div
                                    class="input-wrapper input-wrapper-datepicker"
                                    v-if="another"
                                >
                                    <div class="input__logo-wrapper">
                                        <img
                                            src="/images/calendar.png"
                                            alt=""
                                            class="input__logo j_card-brand"
                                        />
                                    </div>
                                    <label
                                        data-datepicker-min="3"
                                        class="input-label datepicker j_datepicker"
                                    >
                                        <input
                                            required
                                            class="input input--big user_activate"
                                            type="text"
                                            v-model="user_activate"
                                            v-on:change="isChangeActivate"
                                            placeholder="Дата активации полиса"
                                        />
                                        <div class="input__placeholder">
                                            <span>Дата активации полиса</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div
                                class="error"
                                v-bind:key="value"
                                v-for="value in errors"
                            >
                                {{ showError(value) }}
                            </div>
                            <Pay
                                :period="period"
                                :price="fullPrice"
                                method="pay_for_police"
                                :tarrif="tarrif"
                                :status="payment_status"
                                :hidden="another"
                                :user_id="user_id"
                                @isPaying="beforeSend"
                            />
                        </div>
                        <!-- ./ End of form -->
                    </div>
                    <!-- ./ End of wrap -->
                </div>
                <!-- ./ End of container -->
            </section>
        </template>
    </div>
</template>

<script>
import tarrif from "../../components/auth/tarrif";
import Pay from "../../components/payment";
import Insurances from "../../components/auth/insurances";
import DatePickerFile from "../../mixins/datepicker";
import moment from "moment";
import date from "../../mixins/date";
import invalid from "../../mixins/invalid";
import Paymenttype from "../../components/payment/paymentType";
import description from "../../components/payment/description";
export default {
    data: () => ({
        period: "month",
        loading: true,
        phone: "",
        errors: [],
        nextStep: "",
        tarrifs: [],
        data_cart: [],
        address: "",
        appartment: "",
        tarrif_id: 2,
        cart: "",
        tarrif: false,
        another: false,
        payment_status: false,
        user_activate: "",
        fullPrice: null,
        user_id: null
    }),
    mixins: [date, invalid, DatePickerFile],
    components: {
        tarrif,
        Pay,
        Insurances,
        Paymenttype,
        description
    },
    mounted: function() {
        axios.get("/api/v1/plans").then(response => {
            if (response.data) {
                response.data.forEach($tarrif => {
                    this.tarrifs.push($tarrif);
                });

                this.tarrif_id = response.data[0].id;
                this.countPrice();
            } else {
                if (response.data.hasOwnProperty("errors"))
                    response.data.errors.forEach($error => {
                        this.errors.push($error);
                    });
            }
        });

        let data = new URLSearchParams();
        data.append("action", "checking");
        axios.get("/api/v1/send_phone", { params: data }).then(response => {
            if (
                response.data.data.hasOwnProperty("phone") &&
                response.data.data.phone
            ) {
                this.phone = response.data.data.phone;
            }
            if (
                response.data.data.hasOwnProperty("address") &&
                response.data.data.address
            ) {
                this.address = response.data.data.address;
            }
            if (
                response.data.data.hasOwnProperty("appartment") &&
                response.data.data.appartment
            ) {
                this.appartment = response.data.data.appartment;
            }
            if (response.data.data.another_polic) this.another = true;
            if (response.data.data.hasOwnProperty("user_id")) {
                this.user_id = response.data.data.user_id;
            }
        });

        setTimeout(() => {
            this.loading = false;
        }, 500);

        setTimeout(() => {
            this.initDatepickers();
        }, 1000);
        this.onSubmit();
    },
    methods: {
        changePeriodHandler(value) {
            this.period = value;
            const getTarrifsByPeriod = this.tarrifs.filter(
                el => el.period == value
            );

            this.tarrif_id = getTarrifsByPeriod[0].id;
            this.countPrice();
        },
        setDate() {
            document.querySelectorAll(".j_datepicker input").forEach($input => {
                let value = moment(
                    new Date().setDate(new Date().getDate() + 3)
                ).format("DD.MM.YYYY");
                $input.value = value;
                this.user_activate = value;
            });
        },
        isChangeActivate($elem) {
            this.user_activate = $elem.target.value;
        },
        enableButton() {
            var b = document.querySelector(".submit-button");
            b.removeAttribute("disabled");
        },
        previousPage() {
            if (this.$route.query.hasOwnProperty("previously"))
                this.$router.push({
                    path: "/authaddress",
                    query: this.$route.query
                });
            else this.$router.go(-1);
        },
        beforeSend($status) {
            this.payment_status = $status ? $status : false;
            this.validateForm();
            if ($status) this.sendPayment();
            else
                this.errors.push({
                    er:
                        "При оплате произошла ошибка, если вы ранее совершали заказ на сайте, то вам нужно сменить привязку карты в личном кабинете"
                });
        },
        validateForm() {
            this.errors = [];
            if (!this.payment_status) {
                this.errors.push({ er: "Вы не оплатили тариф" });
            }
            if (!this.tarrif_id)
                this.errors.push({ er: "Вы не выбрали тариф" });

            // if( this.cart.length == 0 )
            //   this.errors.push("Вы не ввели карту")

            if (this.another && this.user_activate.length == 0)
                this.errors.push({
                    user_activate: "Вы не ввели дату активации"
                });

            if (this.errors.length !== 0) this.enableButton();

            this.isInvalid("user_activate");
        },
        sendPayment() {
            if (this.errors.length == 0) {
                const data = new URLSearchParams();
                data.append("payment_status", this.payment_status);
                data.append("tarrif_id", this.tarrif_id);
                data.append("user_activate", this.user_activate);
                data.append("action", "payment");

                axios
                    .get("/api/v1/send_phone", { params: data })
                    .then(response => {
                        if (response.data.status) {
                            //if(response.data.phone)

                            if (!this.another) {
                                if (
                                    response.data.data.hasOwnProperty(
                                        "user_id"
                                    ) &&
                                    response.data.data.user_id > 0
                                ) {
                                    this.$router.push("/authpaysuccess");
                                }
                            } else {
                                if (
                                    response.data.data.hasOwnProperty(
                                        "another_done"
                                    )
                                ) {
                                    this.$router.push("/account");
                                }
                            }
                        } else {
                            if (response.data.data.hasOwnProperty("errors"))
                                response.data.data.errors.forEach($error => {
                                    this.errors.push($error);
                                });
                        }
                        this.isInvalid("user_activate");
                        this.isInvalid("err");
                    });
            }
        },
        chosenTarrif($id) {
            if ($id > 0) {
                this.tarrif_id = $id;
                this.countPrice();
                this.setDate();
            }
        },
        countPrice() {
            let $fullPrice = 0;
            if (this.tarrif_id > 0) {
                this.tarrifs.forEach($tarrif => {
                    if ($tarrif.id == this.tarrif_id) {
                        $fullPrice = $tarrif.price;
                        this.tarrif = $tarrif;
                    }
                });
            }
            this.fullPrice = $fullPrice;
        }
    }
};
</script>

<style lang="scss" scoped>
.step-form-card.j_card {
    margin-left: auto;
    margin-right: auto;
}
.step-form-card-buttons {
    margin-top: 0 !important;
}
.j_datepicker input,
.j_datepicker {
    width: 100%;
}
.j_datepicker {
    margin-top: 8px;
}
.input-wrapper-datepicker {
    width: 60%;
    margin: 40px auto;
}
.step-polis .input-wrapper {
    width: 100%;
}
@media (min-width: 700px) {
    .step-polis-wrapper {
        min-width: 360px;
        width: 100%;
    }
}
@media (max-width: 700px) {
    .step-polis-wrapper {
        min-width: 320px;
    }
}
.step-form-inner {
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 15px;
}
.step-form-inner .step-form-data {
    margin-top: 0;
}
.step-form-data {
    margin-bottom: 40px;
}
.step-form-inner .datepicker {
    margin-top: 15px;
}

.for-mobile-inline-flex li:nth-child(1),
.for-mobile-inline-flex li:nth-child(2) {
    flex: 1 1 25% !important;
}
.for-mobile-inline-flex li:nth-child(3) {
    flex: 1 1 50% !important;
}

.plans_flex {
    display: flex;
    width: 100%;
    justify-content: space-between;
    flex-wrap: wrap;
    > div {
        width: calc(50% - 10px);
    }

    @media (max-width: 768px) {
        > div {
            width: 100%;
        }
    }
}

.step-form-tariffs {
    @media (max-width: 768px) {
        margin-top: 0;
    }
}

.step-wrap {
    max-width: 100%;
}
</style>
