<template>
    <div>
        <v-spinner v-if="loading" />
        <template v-else>
            <v-header></v-header>
            <section class="step-section step-section--no-overflow">
                <div class="container">
                    <!-- wrap -->
                    <div class="step-wrap">
                        <h1 class="title">
                            <a
                                @click="previousPage"
                                class="button button-arrow button-arrow--prev"
                            >
                                <svg class="svg">
                                    <use xlink:href="#arrow"></use>
                                </svg>
                            </a>
                            Осталось активировать полис. Для этого введи
                            информацию о себе
                        </h1>

                        <!-- form -->
                        <div class="step-form">
                            <div class="step-polis-wrapper">
                                <div class="step-polis">
                                    <div class="input-wrapper">
                                        <label class="input-label">
                                            <input
                                                required
                                                class="input input--big user_name"
                                                v-model="user_name"
                                                type="text"
                                                placeholder="Имя"
                                            />
                                            <div class="input__placeholder">
                                                <span>Имя</span>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="input-wrapper">
                                        <label class="input-label">
                                            <input
                                                required
                                                class="input input--big user_surname"
                                                v-model="user_surname"
                                                type="text"
                                                placeholder="Фамилия"
                                            />
                                            <div class="input__placeholder">
                                                <span>Фамилия</span>
                                            </div>
                                        </label>
                                    </div>
                                    <div
                                        class="input-wrapper input-wrapper--small"
                                    >
                                        <label class="input-label">
                                            <input
                                                required
                                                class="input input--big j_date-mask user_birsday"
                                                maxlength="10"
                                                v-on:change="isChangeBirsday"
                                                v-model="user_birsday"
                                                type="text"
                                                placeholder="Дата активации полиса"
                                            />

                                            <div class="input__placeholder">
                                                <span>Дата рождения</span>
                                            </div>
                                        </label>
                                    </div>
                                    <div
                                        class="input-wrapper input-wrapper-datepicker input-wrapper--small"
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
                                                v-on:change="isChangeActivate"
                                                v-model="user_activate"
                                                placeholder="Дата активации полиса"
                                            />
                                            <div class="input__placeholder">
                                                <span
                                                    >Дата активации полиса</span
                                                >
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="error"
                                :key="value"
                                v-for="value in errors"
                            >
                                {{ showError(value) }}
                            </div>
                            <button
                                class="button submit-button"
                                type="button"
                                v-on:click="sendActivate()"
                            >
                                Активировать полис
                            </button>
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
import axios from "axios";
import DatePickerFile from "../../mixins/datepicker";
//import 'vue2-datepicker/index.css'
//import 'vue2-datepicker/locale/ru'
import moment from "moment";
//import DatePicker from "../../components/date"

export default {
    data: () => ({
        loading: true,
        user_name: "",
        user_surname: "",
        user_birsday: "",
        user_activate: "",
        errors: [],
        nextStep: ""
    }),
    comoponents: {
        Date
    },
    mixins: [DatePickerFile],
    mounted: function() {
        setTimeout(() => {
            this.loading = false;
        }, 500);
        setTimeout(() => {
            this.initDatepickers();
            this.setDate();
        }, 1000);
        this.onSubmit();
    },
    methods: {
        setDate() {
            document.querySelectorAll(".j_datepicker input").forEach($input => {
                let value = moment(
                    new Date().setDate(new Date().getDate() + 3)
                ).format("DD.MM.YYYY");
                $input.value = value;
                this.user_activate = value;
            });
        },
        isChangeBirsday($elem) {
            console.log($elem.target.value);
            this.user_birsday = $elem.target.value;
        },
        isChangeActivate($elem) {
            this.user_activate = $elem.target.value;
        },
        previousPage() {
            this.$router.push({
                path: "/authpayment",
                query: {
                    previously: "/authpayment"
                }
            });
        },
        sendActivate() {
            let user_birsday = "";
            let user_activate = "";
            this.errors = [];
            if (this.user_name.length == 0)
                this.errors.push({ user_name: "Вы не ввели имя" });

            if (this.user_surname.length == 0)
                this.errors.push({ user_surname: "Вы не ввели фамилию" });

            if (this.user_birsday.length == 0)
                this.errors.push({ user_birsday: "Вы не ввели дату рождения" });
            else {
                user_birsday = this.user_birsday;
                console.log(Date.parse(user_birsday));
                if (!this.validate_date(user_birsday))
                    this.errors.push({
                        user_birsday: "Вы неверную дату рождения"
                    });
            }

            if (!this.user_activate) {
                this.errors.push({
                    user_activate: "Вы не ввели дату активации полиса"
                });
            } else user_activate = this.user_activate;

            //console.log(this.errors)
            this.isInvalid("user_name");
            this.isInvalid("user_surname");
            this.isInvalid("user_birsday");
            this.isInvalid("user_activate");

            if (this.errors.length == 0) {
                const data = new URLSearchParams();
                data.append("user_name", this.user_name);
                data.append("user_surname", this.user_surname);
                data.append("user_birsday", user_birsday);
                data.append("user_activate", user_activate);
                data.append("action", "activate");

                axios
                    .get("/api/v1/send_phone", { params: data })
                    .then(response => {
                        //console.log(response)
                        if (response.data.status) {
                        } else {
                            if (response.data.data.hasOwnProperty("errors"))
                                response.data.data.errors.forEach($error => {
                                    this.errors.push($error);
                                });
                        }
                        this.isInvalid("user_name");
                        this.isInvalid("user_surname");
                        this.isInvalid("user_birsday");
                        this.isInvalid("user_activate");
                    });
            }
        },
        validate_date(value) {
            var arrD = value.split(".");
            arrD[1] -= 1;
            var d = new Date(arrD[2], arrD[1], arrD[0]);
            return (
                d.getFullYear() == arrD[2] &&
                d.getMonth() == arrD[1] &&
                d.getDate() == arrD[0]
            );
        }
    }
};
</script>

<style scoped>
@import "/css/datepicker.minimal.css";
</style>
