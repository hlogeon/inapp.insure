<template>
    <div class="plans">
        <h5>Что включено?</h5>

        <div class="row">
            <span>Ремонт и отделка</span>
            <div>
                <span v-if="cid === 1 || cid === 2">300 000 ₽</span>
                <span v-else-if="cid === 3 || cid === 4">600 000 ₽</span>
                <span v-else-if="cid === 5 || cid === 6">900 000 ₽</span>
                <img svg-inline src="../assets/checked.svg" />
            </div>
        </div>
        <div class="row">
            <span>Техника и личные вещи</span>
            <div>
                <span v-if="cid === 1 || cid === 2">300 000 ₽</span>
                <span v-else-if="cid === 3 || cid === 4">600 000 ₽</span>
                <span v-else-if="cid === 5 || cid === 6">900 000 ₽</span>
                <img svg-inline src="../assets/checked.svg" />
            </div>
        </div>
        <div class="row">
            <span>Вред третьим лицам</span>
            <div>
                <span v-if="cid === 1 || cid === 2">300 000 ₽</span>
                <span v-else-if="cid === 3 || cid === 4">600 000 ₽</span>
                <span v-else-if="cid === 5 || cid === 6">900 000 ₽</span>
                <img svg-inline src="../assets/checked.svg" />
            </div>
        </div>
        <div class="row">
            <span>Потеря пригодности жилья</span>
            <div>
                <span v-if="cid === 1 || cid === 2">100 000 ₽</span>
                <span v-else-if="cid === 3 || cid === 4">200 000 ₽</span>
                <span v-else-if="cid === 5 || cid === 6">300 000 ₽</span>
                <img svg-inline src="../assets/checked.svg" />
            </div>
        </div>
        <div class="row">
            <span>Кэшбэк</span>
            <div>
                <span>Безлимит</span
                ><img svg-inline src="../assets/checked.svg" />
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "description",
    props: {
        id: false
    },

    computed: {
        cid() {
            return this.id ? this.id : 1;
        }
    }
};
</script>
<style lang="scss" scoped>
.plans {
    margin-top: 20px;
    background-color: #f1f1f4;
    border-radius: 24px;
    padding: 30px 25px 10px;
    h5 {
        font-family: SFProRounded, sans-serif;
        font-size: 26px;
        line-height: 31px;
        color: #222;
        font-weight: 700;
        margin: 0 0 10px;
    }

    .row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
        color: #222;
        padding: 15px 0;
        span {
            font-size: 18px;
            font-family: SFProRounded, sans-serif;
            color: #222;
        }
        img {
            margin-left: 12px;
            display: inline-block;
            transform: translateY(4px);
        }
    }

    @media (max-width: 768px) {
        margin: 5px 0;
        padding: 30px 20px 15px;
        h5 {
            font-size: 16px;
        }
        .row {
            padding: 10px 0;
            span {
                font-size: 12px;
            }

            img {
                width: 11px;
                height: 11px;
                margin-left: 10px;
                transform: translateY(2px);
            }
        }
    }
}
</style>
