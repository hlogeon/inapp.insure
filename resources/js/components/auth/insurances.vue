<template>
    <ul class="step-form-desc-list">
        <li
            class="step-form-desc-item"
            v-for="(insurance, index) in insurances"
            :key="index"
        >
            <p class="step-form-desc-item__text">
                <span class="step-form-desc-item__key">
                    {{ insurance.name }}
                </span>
                <em class="step-form-desc-item__value">
                    {{ insurance.price }} ₽
                </em>
            </p>
        </li>
    </ul>
</template>

<script>
export default {
    data: () => ({
        insurances: []
    }),
    mounted() {
        axios.get("/api/v1/get_insurances_public").then(response => {
            if (response.data.status) this.insurances = response.data.data;
        });
    }
};
</script>
