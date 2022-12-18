import { createRouter, createWebHistory } from "vue-router";
import { useUserStore } from "../stores/user.js";

import Dashboard from "../views/Dashboard.vue";
import CustomerDashboard from "../views/CustomerDashboard.vue";
import DeliveryDashboard from "../views/DeliveryDashboard.vue";
import Orders from "../components/orders/Orders.vue";
import Order from "../components/orders/Order.vue";
import ChangePassword from "../components/auth/ChangePassword.vue";
import ChangeProfile from "../components/auth/ChangeProfile.vue";
import Login from "../components/auth/Login.vue";
import Users from "../components/users/Users.vue";
import User from "../components/users/User.vue";
import Register from "../components/auth/Register.vue";
import Products from "../components/products/Products.vue";
import Product from "../components/products/Product.vue";
import Menu from "../components/menu/Menu.vue";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: "/",
            name: "Dashboard",
            component: Dashboard,
        },
        {
            path: "/",
            name: "CustomerDashboard",
            component: CustomerDashboard,
        },
        {
            path: "/",
            name: "DeliveryDashboard",
            component: DeliveryDashboard,
        },
        {
            path: "/users",
            name: "Users",
            component: Users,
        },
        {
            path: "/products",
            name: "Products",
            component: Products,
        },
        {
            path: "/menu",
            name: "Menu",
            component: Menu,
        },
        {
            path: "/users/:id",
            name: "User",
            component: User,
            props: (route) => ({ id: parseInt(route.params.id) }),
        },
        {
            path: "/login",
            name: "Login",
            component: Login,
        },
        {
            path: "/password",
            name: "ChangePassword",
            component: ChangePassword,
        },
        {
            path: "/profile",
            name: "ChangeProfile",
            component: ChangeProfile,
        },
        {
            path: "/register",
            name: "Register",
            component: Register,
        },
        {
            path: "/orders",
            name: "Orders",
            component: Orders,
        },
        {
            path: "/orders/current",
            name: "CurrentOrder",
            component: Orders,
            props: { onlyCurrentOrders: true, ordersTitle: "Current Order" },
        },
        {
            path: "/order/new",
            name: "NewOrder",
            component: Order,
            props: { id: -1 },
        },
        {
            path: "/products/new",
            name: "newProduct",
            component: Product,
            props: { id: -1 },
        },
        {
            path: "/users/new",
            name: "newUser",
            component: User,
            props: { id: -1 },
        },
        {
            path: "/orders/:id",
            name: "Order",
            component: Order,
            props: (route) => ({ id: parseInt(route.params.id) }),
        },
        {
            path: "/products/:id",
            name: "Product",
            component: Product,
            props: (route) => ({ id: parseInt(route.params.id) }),
        },

        {
            path: "/:pathMatch(.*)*",
            name: "Forbidden",
            component: () => import("@/views/Forbidden.vue"),
        },
    ],
})

let handlingFirstRoute = true

router.beforeEach(async (to, from, next) => {
    const userStore = useUserStore()

    if (handlingFirstRoute) {
        handlingFirstRoute = false
        await userStore.restoreToken()
    }

    if (to.name == "Dashboard" && (userStore.user && userStore.user.type == "C")) {
        next({
            name: "CustomerDashboard"
        })
        return
    }
    if (to.name == "Dashboard" && (userStore.user && userStore.user.type == "ED")) {
        next({
            name: "DeliveryDashboard"
        })
        return
    }

    if (to.name == "Login") {
        next()
        return
    }

    if (to.name == "newProduct") {
        if (userStore.user === null || userStore.user.type != "EM") {
            next({
                name: "Forbidden",
                params: { pathMatch: to.path.substring(1).split("/") },
                query: to.query,
                hash: to.hash,
            })
            return
        }
    }

    if (to.name == "Products") {
        if (userStore.user === null || userStore.user.type != "EM") {
            next({
                name: "Forbidden",
                params: { pathMatch: to.path.substring(1).split("/") },
                query: to.query,
                hash: to.hash,
            })
            return
        }
    }

    if (to.name == "Product") {
        if (userStore.user.type == "EM") {
            next()
            return
        }
        next({
            name: "Forbidden",
            params: { pathMatch: to.path.substring(1).split("/") },
            query: to.query,
            hash: to.hash,
        })
        return
    }

    if (to.name == "Order") {
        if (userStore.user.type == "EM" || userStore.user.type == "C") {
            next()
            return
        }
        next({
            name: "Forbidden",
            params: { pathMatch: to.path.substring(1).split("/") },
            query: to.query,
            hash: to.hash,
        })
        return
    }

    if (to.name == "Users") {
        if (userStore.user === null || userStore.user.type != "EM") {
            next({
                name: "Forbidden",
                params: { pathMatch: to.path.substring(1).split("/") },
                query: to.query,
                hash: to.hash,
            })
            return
        }
    }

    if (to.name == "NewOrder") {
        if (userStore.user === null) {
            next()
            return
        }
        if (userStore.user.type == "EC" || userStore.user.type == "ED") {
            next({
                name: "Forbidden",
                params: { pathMatch: to.path.substring(1).split("/") },
                query: to.query,
                hash: to.hash,
            })
            return
        }
    }

    if (to.name == "User") {
        if (
            userStore.user === null ||
            userStore.user.type != "EM" ||
            userStore.user.id == to.params.id
        ) {
            next({
                name: "Forbidden",
                params: { pathMatch: to.path.substring(1).split("/") },
                query: to.query,
                hash: to.hash,
            });
            return
        }
        if (userStore.user.type == "EM" || userStore.user.id == to.params.id) {
            next()
            return
        }
    }

    next()
})

export default router
