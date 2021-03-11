import vueRouter from "vue-router";
import Vue from "vue";

Vue.use(vueRouter);

import Index from "./views/pages/index";
import AuthPhone from "./views/auth/mobile";
import AuthSms from "./views/auth/sms";
import AuthAddress from "./views/auth/address";
import AuthPayment from "./views/auth/payment";
import AuthPaySuccess from "./views/auth/success";
import AuthActive from "./views/auth/activate";
import Account from "./views/account/index";
import ChangeMobile from "./views/account/mobile";
import ChangeSms from "./views/account/sms";
import ChangeCart from "./views/account/cart";
import Discribe from "./views/account/discribe";
import DiscribeAgreed from "./views/account/discribeagreed";
import DiscribeVote from "./views/account/discribevote";
import Settings from "./views/account/settings";
import Cashback from "./views/pages/cashback";

import NotFound from "./views/404";

const routes = [
    {
        path: "/",
        component: Index
    },
    {
        path: "/cashback",
        component: Cashback
    },
    {
        path: "/authphone",
        component: AuthPhone,
        meta: { logout: true }
    },
    {
        path: "/authsms",
        component: AuthSms,
        meta: { logout: true }
    },
    {
        path: "/authaddress",
        component: AuthAddress
    },
    {
        path: "/authpayment",
        component: AuthPayment,
        meta: { middlewareAuth: true }
    },
    {
        path: "/authpaysuccess",
        component: AuthPaySuccess,
        meta: { middlewareAuth: true }
    },
    {
        path: "/authactive",
        component: AuthActive,
        meta: { middlewareAuth: true }
    },

    {
        path: "/account",
        component: Account,
        meta: { middlewareAuth: true }
    },
    {
        path: "/discribe/:id",
        component: Discribe,
        meta: { middlewareAuth: true }
    },
    {
        path: "/discribagreed/:id",
        component: DiscribeAgreed,
        meta: { middlewareAuth: true }
    },
    {
        path: "/changephone",
        component: ChangeMobile,
        meta: { middlewareAuth: true }
    },
    {
        path: "/changesms",
        component: ChangeSms,
        meta: { middlewareAuth: true }
    },
    {
        path: "/changecart",
        component: ChangeCart,
        meta: { middlewareAuth: true }
    },
    {
        path: "/discribvote/:id",
        component: DiscribeVote,
        meta: { middlewareAuth: true }
    },
    {
        path: "/settings",
        component: Settings,
        meta: { middlewareAuth: true }
    },
    {
        path: "*",
        component: NotFound
    }
];

const router = new vueRouter({
    mode: "history",
    routes
});

import axios from "axios";
import Auth from "./auth.js";

var title_inapp = "Страхование квартиры за 399 ₽ в месяц!";

router.beforeEach((to, from, next) => {
    document.title = title_inapp;

    if (to.matched.some(record => record.meta.middlewareAuth)) {
        try {
            const objectArray = new Auth(axios);
            objectArray
                .check()
                .then(IsAuth => {
                    if (IsAuth.data.data == 1) {
                        next();
                    } else {
                        next({
                            path: "/authphone",
                            query: { redirect: to.fullPath }
                        });
                    }
                })
                .catch(error => {
                    next({
                        path: "/authphone",
                        query: { redirect: to.fullPath }
                    });
                });
        } catch (e) {}
    } else if (to.matched.some(record => record.meta.logout)) {
        try {
            const objectArray = new Auth(axios);
            objectArray
                .check()
                .then(IsAuth => {
                    if (IsAuth.data.data == 1) {
                        next({
                            path: "/"
                        });
                    } else next();
                })
                .catch(error => {
                    next();
                });
        } catch (e) {}
    } else {
        next();
    }
});

export default router;
