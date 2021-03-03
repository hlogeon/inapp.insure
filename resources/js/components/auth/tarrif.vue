<template>
    <label class="subscribe-plan" v-if="tarrif.period === period">
        <input
            type="radio"
            name="tar"
            :checked="id && id == tarrif.id"
            :id="'tar_' + tarrif.id"
            v-on:change="isTarrif(tarrif.id)"
            :value="tarrif.id"
        />
        <div class="control">
            <div>
                <div class="amount">{{ tarrif.coverage / 1000000 }} млн ₽</div>
                <div class="label">Сумма покрытия</div>
            </div>
            <div>
                <div class="price">
                    {{ tarrif.price.toLocaleString("ru-RU") }} ₽/мес.
                </div>
            </div>
        </div>
    </label>
</template>

<script>
export default {
    props: {
        tarrif: false,
        id: false,
        period: String
    },
    methods: {
        isTarrif($id) {
            this.$emit("tarrifIs", $id);
        }
    }
};
</script>

<style lang="scss" scoped>
.subscribe-plan {
    width: 100%;
    display: block;
    margin: 20px 0;
}
input {
    position: absolute;
    left: -9999px;

    &:checked + .control {
        border: 1px solid #48c9ff;
    }
}
.control {
    cursor: pointer;
    display: flex;
    flex-flow: row;
    align-items: center;
    justify-content: space-between;
    border: 1px solid #f1f1f4;
    border-radius: 24px;
    padding: 19px 30px;
    transition: 0.3s border ease;

    .amount {
        font-family: SFProRounded, sans-serif;
        font-size: 26px;
        line-height: 31px;
        color: #222;
        font-weight: 700;
    }
    .label {
        font-family: SFProDisplay, sans-serif;
        font-size: 18px;
        line-height: 21px;
        color: #94959e;
        font-weight: 400;
        margin-top: 8px;
    }
    .price {
        display: inline-block;
        font-family: SFProDisplay, sans-serif;
        font-size: 20px;
        line-height: 24px;
        color: #222;
        font-weight: 400;
        padding: 8px 15px;
        border-radius: 12px;
        background-color: #f1f1f4;
    }
}
</style>
