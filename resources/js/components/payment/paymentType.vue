<template
    ><div class="subscribe-period-toggle">
        <span>Тип оплаты</span>
        <div class="toggle">
            <label
                ><input
                    type="radio"
                    name="period"
                    value="month"
                    v-model="periodC"
                    @change="changehandler"
                />
                <div class="control">месяц</div></label
            >
            <label
                ><input
                    type="radio"
                    name="period"
                    value="year"
                    v-model="periodC"
                    @change="changehandler"
                />
                <div class="control">год</div></label
            >
            <div
                v-bind:class="[periodC === 'year' ? 'active' : '', 'switch']"
            ></div>
        </div>
        <!-- <Union /> -->
        <img svg-inline src="../assets/union.svg" /></div
></template>

<script>
export default {
    name: "Paymenttype",
    props: {
        period: { type: String }
    },
    methods: {
        changehandler(e) {
            this.$emit("changePeriod", e.target.value);
        }
    },
    computed: {
        periodC: {
            get() {
                return this.period;
            },
            set(newName) {
                return newName;
            }
        }
    }
};
</script>
<style lang="scss" scoped>
.subscribe-period-toggle {
    font-family: SFProRounded, sans-serif;
    margin-top: 40px;
    font-size: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    > span {
        margin-right: 15px;
    }
    .toggle {
        margin-right: 10px;
        height: 50px;
        display: inline-flex;
        align-items: center;
        background-color: #f1f1f4;
        border-radius: 100px;
        overflow: hidden;
        position: relative;
        z-index: 3;

        input {
            position: absolute;
            left: -9999px;
        }

        .control {
            border: none;
            color: #222222;
            display: inline-flex;
            align-items: center;
            height: 50px;
            border-radius: 50px;
            font-family: SFProDisplay, sans-serif;
            font-weight: 500;
            font-size: 24px;
            line-height: 28px;
            padding: 0 20px;
            outline: none;
            cursor: pointer;
            position: relative;
            z-index: 3;
            transition: color 0.25s linear;
        }
        .switch {
            width: 108px;
            height: 100%;
            position: absolute;
            left: 0%;
            top: 0;
            background: #2ec86b;
            z-index: 0;
            transition: all 0.25s linear;
            border-radius: 50px;
            &.active {
                left: 108px;
                @media (max-width: 768px) {
                    width: 75.54px;
                    left: 75.54px;
                }
            }
            @media (max-width: 768px) {
                width: 75.54px;
            }
        }

        input:checked + .control {
            color: #fff;
        }
    }

    @media (max-width: 768px) {
        flex-wrap: wrap;
        justify-content: flex-start;
        > span {
            font-size: 16px;
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        .toggle {
            height: 30px;
            .control {
                height: 30px;
                font-size: 16px;
                line-height: 19px;
                height: 30px;
                padding: 0 15px;
            }
        }
    }
}
</style>
