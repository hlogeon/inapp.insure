<template>
    <div class="promocode-wrapper">
        <input
            v-bind:class="{ focus: inputValue.length }"
            v-model="inputValue"
            id="promocode"
            type="text"
        />
        <label for="promocode">Промокод</label>
        <button
            v-if="inputValue.length > 3"
            @click="getPromoCode"
            type="button"
        >
            Применить
        </button>

        <div v-if="success" class="alert success">
            Промокод успешно применен
        </div>
        <div v-if="error" class="alert error">
            Этот промокод недействителен
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    name: "Promocode",
    props: {
        tarrif_id: null
    },
    data() {
        return {
            inputValue: "",
            error: false,
            success: false
        };
    },
    methods: {
        getPromoCode() {
            const params = new URLSearchParams();
            params.append("code", this.inputValue);
            params.append("plan", this.tarrif_id);
            axios
                .get("/api/v1/promocode/activate", { params: params })
                .then(response => {
                    const { data } = response;
                    if (data.status) {
                        this.success = true;
                        this.error = false;
                        this.$emit("setPromoCode", data.data.final_price);
                    }
                })
                .catch(err => {
                    this.success = false;
                    this.error = true;
                })
                .finally(() => {
                    setTimeout(() => {
                        this.success = false;
                        this.error = false;
                    }, 2000);
                });
        }
    }
};
</script>

<style lang="scss" scoped>
.promocode-wrapper {
    position: relative;
    margin-bottom: 10px;
    font-family: SFProDisplay;

    input {
        background: #f1f1f4;
        border: none;
        width: 490px;
        height: 55px;
        border-radius: 16px;
        box-sizing: border-box;
        padding: 28px 20px 8px 20px;
        vertical-align: baseline;
        color: #222222;
        font-size: 18px;
        border: 1px solid transparent;
        text-transform: uppercase;
        font-family: SFProDisplay;
        font-weight: 400;
        padding-right: 140px;
        &:focus {
            outline: none;
            background: #fff;
            border-color: #d0d2d6;
        }
        &:focus + label,
        &.focus + label {
            top: 8px;
            font-size: 12px;
        }
    }

    label {
        color: #94959e;
        font-size: 18px;
        font-family: SFProDisplay;
        font-weight: 400;
        position: absolute;
        left: 20px;
        top: 17px;
        transition: all 0.2s linear;
    }

    button {
        font-weight: 600;
        font-size: 18px;
        position: absolute;
        right: 20px;
        top: 15px;
        background: transparent;
        border: none;
        &:focus {
            outline: none;
        }
    }

    .alert {
        font-size: 16px;
        font-weight: 400;
        text-align: center;
        margin: 0%;
        margin-top: 10px;
        &.error {
            color: #f54064;
        }
        &.success {
            color: #2ec86b;
        }
    }

    @media (max-width: 768px) {
        width: 100%;
        input {
            width: 100%;
            padding-right: 120px;
        }
        label {
            font-size: 14px;
        }
        button {
            font-size: 14px;
            top: 18px;
        }
    }
}
</style>
