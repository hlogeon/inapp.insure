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
                            v-if="prev"
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
                            v-if="prev"
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
                                class="steps-list-item steps-list-item--current"
                            >
                                <i class="steps-list-item__num"></i>
                            </li>
                        </ul>
                        <h1 class="title">
                            <a
                                @click="previousPage"
                                v-if="prev"
                                class="button button-arrow button-arrow--prev"
                            >
                                <svg class="svg">
                                    <use xlink:href="#arrow"></use>
                                </svg>
                            </a>
                            <span class="for-desktop">
                                Введи свой номер <br />
                                мобильного телефона
                            </span>

                            <span class="for-mobile">
                                Введи свой номер мобильного телефона
                            </span>
                        </h1>

                        <!-- form -->
                        <div class="step-form">
                            <div class="step-phone-wrapper">
                                <div class="input-wrapper">
                                    <label class="input-label">
                                        <input
                                            required
                                            class="input phone input--big j_mask"
                                            type="tel"
                                            v-model="phone"
                                            placeholder="Номер телефона"
                                        />
                                        <div class="input__placeholder">
                                            <span>Номер телефона</span>
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
                            <button
                                class="button submit-button"
                                type="button"
                                :disabled="disabled"
                                v-on:click="sendPhoneAuth()"
                            >
                                Продолжить
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
export default {
    data: () => ({
        loading: true,
        phone: "",
        errors: [],
        nextStep: "",
        prev: false,
        disabled: false
    }),
    mounted: function() {
        if (
            this.$route.query.hasOwnProperty("redirect") &&
            this.$route.query.redirect == "/authpayment"
        )
            this.prev = true;
        setTimeout(() => {
            this.loading = false;
        }, 500);
        setTimeout(() => {
            const inputs = document.querySelectorAll(".j_mask"); // Inputs

            if (inputs.length) {
                // eslint-disable-next-line no-undef
                const event = new Event("input");

                inputs.forEach(input => {
                    input.addEventListener("input", mask, false);
                    input.addEventListener("focus", mask, false);
                    input.addEventListener("blur", mask, false);
                    input.addEventListener("keydown", mask, false);

                    if (input.value !== "") {
                        input.dispatchEvent(event);
                        input.blur();
                    }
                });
            }

            function setCursorPosition(pos, elem) {
                elem.focus();
                if (elem.setSelectionRange) elem.setSelectionRange(pos, pos);
                else if (elem.createTextRange) {
                    const range = elem.createTextRange();
                    range.collapse(true);
                    range.moveEnd("character", pos);
                    range.moveStart("character", pos);
                    range.select();
                }
            }

            function mask(event) {
                if (this.selectionStart < 4) event.preventDefault();
                const matrix = "+7 (___) ___-__-__";
                let i = 0;
                const def = matrix.replace(/\D/g, "");
                let val = this.value.replace(/\D/g, "");

                if (def.length >= val.length) val = def;
                this.value = matrix.replace(/[_\d]/g, function(a) {
                    return i < val.length ? val.charAt(i++) : a;
                });
                i = this.value.indexOf("_");
                if (event.keyCode === 8)
                    i = this.value.lastIndexOf(val.substr(-1)) + 1;
                if (i !== -1) {
                    i < 5 && (i = 4);
                    this.value = this.value.slice(0, i);
                }
                if (event.type === "blur") {
                    if (this.value.length < 5) this.value = "";
                } else setCursorPosition(this.value.length, this);
            }
        }, 1000);
        this.onSubmit();
    },
    methods: {
        previousPage() {
            return this.$router.go(-1);
        },
        sendPhoneAuth() {
            let regExp = /^\+7\s\(9[0-9\-\s\)]*$/;
            this.errors = [];
            if (!regExp.test(this.phone))
                this.errors.push("Некорректно введен телефон");

            this.isInvalid("phone");

            if (this.errors.length == 0) {
                this.disabled = true;
                const data = new URLSearchParams();
                data.append("phone", this.phone);
                data.append("action", "phone");
                data.append("redirect", this.$route.query.redirect);

                axios
                    .get("/api/v1/send_phone", { params: data })
                    .then(response => {
                        console.log(response);
                        if (response.data.status) {
                            if (response.data.data.phone)
                                if (
                                    response.data.data.hasOwnProperty(
                                        "nextStep"
                                    ) &&
                                    response.data.data.nextStep.length > 0
                                )
                                    this.$router.push({
                                        path: response.data.data.nextStep,
                                        query: {
                                            previously: this.$route.query
                                                .redirect
                                        }
                                    });
                                else {
                                    this.$router.push({
                                        path: "/authsms",
                                        query: this.$route.query
                                    });
                                }
                        } else {
                            this.disabled = false;
                            if (response.data.data.hasOwnProperty("errors"))
                                response.data.data.errors.forEach($error => {
                                    this.errors.push($error);
                                });
                        }

                        this.isInvalid("phone");
                    });
            }
        }
    }
};
</script>

<style scoped="">
.for-mobile-inline-flex li:nth-child(1) {
    flex: 1 1 25% !important;
}
</style>
