<template>
    <div>
        <a href="/account" class="button button--small" v-if="!auth">
            Войти
        </a>
        <template v-else>
            <div class="header-controls">
                <div id="profile-dropdown" class="profile dropdown j_dropdown">
                    <div
                        data-dropdown-target="#profile-dropdown"
                        role="button"
                        class="profile__button dropdown__button"
                    >
                        <span class="profile__letter" v-html="setName()"></span>
                    </div>

                    <div class="profile__content dropdown__content">
                        <span class="profile__content-title">
                            <router-link to="/account">
                                Мои полисы
                            </router-link>
                        </span>
                        <ul class="profile__list">
                            <li class="profile__item" v-if="user.cashback <= 0">
                                <a
                                    href="/cashback"
                                    class="profile__link"
                                    title="inapp"
                                >
                                    <div class="profile-points">
                                        <span class="profile-points__text">
                                            Кэшбэк
                                        </span>
                                    </div>
                                </a>
                            </li>
                            <li class="profile__item" v-else>
                                <a href="/cashback" class="logo" title="inapp">
                                    <div
                                        class="profile-points profile-points--colors"
                                    >
                                        <span class="profile-points__text"
                                            >Кэшбэк</span
                                        >
                                    </div>
                                </a>
                            </li>
                            <li class="profile__item" style="display: none;">
                                <a href="/account" class="profile__link">
                                    Мои полисы
                                </a>
                            </li>
                            <li class="profile__item">
                                <a href="/settings" class="profile__link">
                                    Настройки
                                </a>
                            </li>
                            <li class="profile__item">
                                <a
                                    href="javascript:;"
                                    v-on:click="logout()"
                                    class="profile__link"
                                >
                                    Выйти
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
export default {
    data: () => ({
        auth: false,
        user: null
    }),
    mounted: function() {
        axios.get("/api/v1/get_user_data").then(response => {
            //console.log('res', response)
            if (response.data.status) {
                this.auth = true;
                this.user = response.data.data;

                //widget
                window.intercomSettings = {
                    app_id: "jyugn4xp",
                    name: response.data.data.user_name, // Full name
                    email: response.data.data.user_email, // Email address
                    created_at: Date.parse(response.data.data.created_at) // Signup date as a Unix timestamp
                };
                (function() {
                    var w = window;
                    var ic = w.Intercom;
                    if (typeof ic === "function") {
                        ic("reattach_activator");
                        ic("update", w.intercomSettings);
                    } else {
                        var d = document;
                        var i = function() {
                            i.c(arguments);
                        };
                        i.q = [];
                        i.c = function(args) {
                            i.q.push(args);
                        };
                        w.Intercom = i;
                        var l = function() {
                            var s = d.createElement("script");
                            s.type = "text/javascript";
                            s.async = true;
                            s.src =
                                "https://widget.intercom.io/widget/jyugn4xp";
                            var x = d.getElementsByTagName("script")[0];
                            x.parentNode.insertBefore(s, x);
                        };
                        if (w.attachEvent) {
                            w.attachEvent("onload", l);
                        } else {
                            w.addEventListener("load", l, false);
                        }
                    }
                })();
            }
            try {
                setTimeout(() => {
                    document
                        .querySelector("[data-dropdown-target]")
                        .addEventListener("click", function() {
                            document
                                .querySelector("#profile-dropdown")
                                .classList.add("active");
                            //console.log('active')
                            var firstClick = true;
                            var dropping = function(event) {
                                console.log(firstClick);
                                if (!firstClick) {
                                    document
                                        .querySelector("#profile-dropdown")
                                        .classList.remove("active");
                                    document.removeEventListener(
                                        "click",
                                        dropping
                                    );
                                    document.body.click();
                                }
                                firstClick = false;
                            };
                            document.addEventListener("click", dropping);

                            //setTimeout(() => {

                            //}, 200)
                        });
                }, 200);
            } catch (e) {}
        });
    },
    methods: {
        logout() {
            if (this.auth) {
                axios.get("/api/v1/logout").then(response => {
                    this.auth = false;
                    this.$router.push("/");
                });
            }
        },
        setName() {
            if (this.user.user_name != null)
                return "" + this.user.user_name[0].toUpperCase();
            else
                return "<img style='width: 20px;height: auto' src='/images/lk-user.svg'>";
        },
        GetNoun(number, one, two, five) {
            number = Math.abs(number);
            number %= 100;
            if (number >= 5 && number <= 20) {
                return five;
            }
            number %= 10;
            if (number == 1) {
                return one;
            }
            if (number >= 2 && number <= 4) {
                return two;
            }
            return five;
        }
    }
};
</script>

<style>
.profile__content-title a {
    color: #000;
}
.profile__content-title a:hover {
    color: #2ec86b;
}
</style>
