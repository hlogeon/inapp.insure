<template>
    <div>
        <v-spinner v-if="loading" />
        <template v-else>
            <v-header></v-header>
            <main>
                <!-- polis -->
                <section class="polis-section" style="padding-bottom: 0;">
                    <div class="container">
                        <!-- wrap -->
                        <div class="polis-wrap">
                            <div class="polis-content">
                                <!-- addresses -->
                                <div class="polis-addresses-wrapper">
                                    <h2 class="title">
                                        <template
                                            v-if="
                                                current &&
                                                    current.subscribed == 0
                                            "
                                        >
                                            Автопродление отключено
                                        </template>
                                        <template v-else>
                                            Привет<template v-if="name"
                                                >, {{ name }}</template
                                            >!
                                        </template>
                                    </h2>

                                    <template v-if="current">
                                        <Polices
                                            :polices="polices"
                                            @chosenPolice="setId"
                                            :id="id"
                                        />
                                    </template>
                                    <template
                                        v-if="
                                            current && current.subscribed == 0
                                        "
                                    >
                                        <div
                                            class="step-form-card-wrap j_card subscribe-form"
                                        >
                                            <div class="input-wrapper valid">
                                                <label class="input-label">
                                                    <div
                                                        class="input__logo-wrapper"
                                                        v-if="
                                                            current.card.type ==
                                                                'Visa'
                                                        "
                                                    >
                                                        <img
                                                            src="/images/visa.svg"
                                                            alt=""
                                                            class="input__logo j_card-brand"
                                                        />
                                                    </div>
                                                    <div
                                                        class="input__logo-wrapper"
                                                        v-else
                                                    >
                                                        <img
                                                            src="/images/master-card-colored.svg"
                                                            alt=""
                                                            class="input__logo j_card-brand"
                                                        />
                                                    </div>
                                                    <input
                                                        required="required"
                                                        :value="
                                                            current.card.number
                                                                ? '**** **** **** ' +
                                                                  current.card
                                                                      .number
                                                                : ''
                                                        "
                                                        type="text"
                                                        placeholder="Номер карты"
                                                        class="input input--medium j_card-number"
                                                    />
                                                    <div
                                                        class="input__placeholder"
                                                    >
                                                        <span>Номер карты</span>
                                                    </div>
                                                </label>
                                                <button
                                                    type="button"
                                                    class="button"
                                                    @click="
                                                        enableSubscribe(
                                                            current.id
                                                        )
                                                    "
                                                >
                                                    Включить автопродление -
                                                    <template
                                                        v-if="current.tarrif"
                                                        >{{
                                                            current.tarrif.price
                                                        }}
                                                        ₽</template
                                                    >
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <!-- ./ End of addresses -->

                                <!-- title -->
                                <div
                                    class="polis__title-wrapper"
                                    v-if="!current"
                                    style="margin-bottom: 65px;"
                                >
                                    <h1 class="title polis__title">
                                        Добавь свой первый полис
                                    </h1>
                                    <router-link
                                        to="/authaddress"
                                        class="button button--plus"
                                    >
                                        <svg class="svg">
                                            <use xlink:href="#plus"></use>
                                        </svg>
                                    </router-link>
                                </div>
                                <!-- ./ End of title -->

                                <!-- card -->
                                <div
                                    class="polis-card"
                                    v-if="status_id == 4"
                                    style="max-width: 550px;"
                                >
                                    <label class="input-label">
                                        <div
                                            class="input__logo-wrapper"
                                            v-if="current.card.type == 'Visa'"
                                        >
                                            <img
                                                src="/images/visa.svg"
                                                alt=""
                                                class="input__logo j_card-brand"
                                            />
                                        </div>
                                        <div class="input__logo-wrapper" v-else>
                                            <img
                                                src="/images/master-card-colored.svg"
                                                alt=""
                                                class="input__logo j_card-brand"
                                            />
                                        </div>

                                        <input
                                            required="required"
                                            :value="
                                                current.card.number
                                                    ? '**** **** **** ' +
                                                      current.card.number
                                                    : ''
                                            "
                                            type="text"
                                            placeholder="Номер карты"
                                            class="input input--medium j_card-number"
                                        />
                                        <div class="input__placeholder">
                                            <span>Номер карты</span>
                                        </div>
                                    </label>
                                    <Pay
                                        :price="
                                            current.tarrif.price *
                                                current.tarrif.per_month
                                        "
                                        method="pay_for_police"
                                        :tarrif_id="current.tarrif.id"
                                        :hidden="another"
                                        @isPaying="beforeSend"
                                    />
                                </div>
                                <!-- ./ End of card -->

                                <InsuranceList
                                    :type="type"
                                    :current="current"
                                    :id="id"
                                    :insurances="insurances"
                                />
                            </div>
                            <div class="polis__img-wrapper" v-if="!current">
                                <img
                                    src="/images/polis_img1.svg"
                                    alt=""
                                    class="polis__img"
                                />
                            </div>
                            <template v-if="current">
                                <div
                                    class="polis__img-wrapper"
                                    v-if="type && current.subscribed == 0"
                                >
                                    <img
                                        src="/images/discribed.svg"
                                        alt=""
                                        class="polis__img"
                                    />
                                </div>
                                <div
                                    class="polis__img-wrapper"
                                    v-else-if="type && current.subscribed == 1"
                                >
                                    <img
                                        src="/images/man-active.svg"
                                        alt=""
                                        class="polis__img"
                                    />
                                </div>
                                <div class="polis__img-wrapper" v-else>
                                    <img
                                        src="/images/man-disactive.svg"
                                        alt=""
                                        class="polis__img"
                                    />
                                </div>
                            </template>
                        </div>
                    </div>
                </section>

                <Risks :type="type" />

                <template v-if="current">
                    <Info :type="type" :current="current" />
                </template>
            </main>
            <v-footer />
        </template>
    </div>
</template>

<script>
import Polices from "../../components/account/polices_list";
import InsuranceList from "../../components/account/insurance_list";
import Risks from "../../components/account/risks";
import Pay from "../../components/payment";
import Info from "../../components/account/infoPolic";
import popupFile from "../../mixins/popup";

export default {
    data: () => ({
        id: null,
        loading: true,
        status_id: null,
        type: "",
        polices: [],
        current: false,
        info: [],
        name: "",
        insurances: []
    }),
    mixins: [popupFile],
    components: {
        InsuranceList,
        Risks,
        Pay,
        Info,
        Polices
    },
    mounted() {
        this.updateAccount();
        setTimeout(() => {
            this.loading = false;
        }, 500);
    },
    methods: {
        setId($id) {
            if ($id > 0) {
                this.id = $id;
                this.updateAccount();
            }
        },
        setType($status_id) {
            if ($status_id == 1 || $status_id == 2) this.type = true;
            else this.type = false;
        },

        getInsurances($id) {
            axios.get("/api/v1/get_insurances?id=" + $id).then(response => {
                if (response.data.status) {
                    this.insurances = response.data.data;
                    console.log(" this.insurances", this.insurances);
                }
            });
        },

        updateAccount() {
            const data = new URLSearchParams();
            data.append("id", this.id);

            axios
                .get("/api/v1/get_polices", { params: data })
                .then(response => {
                    if (response.data.status) {
                        let $data = response.data.data;
                        this.polices = $data.polices;
                        // this.setId(this.polices[0].id);
                        this.getInsurances(
                            this.id ? this.id : $data.polices[0].id
                        );
                        if (
                            $data.user.hasOwnProperty("user_name") &&
                            $data.user.user_name
                        )
                            this.name = $data.user.user_name;

                        if ($data.hasOwnProperty("current")) {
                            this.setType($data.current.status_id);
                            this.status_id = $data.current.status_id;
                            this.current = $data.current;
                        } else {
                            this.type = true;
                        }
                    }
                });
        },
        enableSubscribe($id) {
            const data = new URLSearchParams();
            data.append("id", $id);

            axios
                .get("/api/v1/enable_subscribe", { params: data })
                .then(response => {
                    if (response.data.status) {
                        this.updateAccount();
                        new StatusPopup({
                            text: "Автопродление включено",
                            delay: 3000
                        });
                    }
                });
        }
    }
};
</script>

<style scoped="">
.step-form-card.j_card {
    margin-left: 0;
    margin-right: 0;
}
.subscribe-form {
    display: inline-block;
    margin-top: 40px;
}
.subscribe-form button {
    margin-top: 35px;
}
</style>
