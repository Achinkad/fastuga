import { inject } from "vue";
import { createRouter, createWebHistory } from "vue-router";
import { useOrderStore } from "../stores/order.js";
import { useUserStore } from "../stores/user.js";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "Dashboard",
      component: () => import("../views/Dashboard.vue"),
    },
    {
      path: "/",
      name: "CustomerDashboard",
      component: () => import("../views/CustomerDashboard.vue"),
    },
    {
      path: "/",
      name: "DeliveryDashboard",
      component: () => import("../views/DeliveryDashboard.vue"),
    },
    {
      path: "/",
      name: "AnonymousDashboard",
      component: () => import("../views/AnonymousDashboard.vue"),
    },
    {
      path: "/",
      name: "ChefDashboard",
      component: () => import("../views/ChefDashboard.vue"),
    },
    {
      path: "/users",
      name: "Users",
      component: () => import("../components/users/Users.vue"),
    },
    {
      path: "/products",
      name: "Products",
      component: () => import("../components/products/Products.vue"),
    },
    {
      path: "/menu",
      name: "Menu",
      component: () => import("../components/menu/Menu.vue"),
    },
    {
      path: "/users/:id",
      name: "User",
      component: () => import("../components/users/User.vue"),
      props: (route) => ({ id: parseInt(route.params.id) }),
    },
    {
      path: "/login",
      name: "Login",
      component: () => import("../components/auth/Login.vue"),
    },
    {
      path: "/password",
      name: "ChangePassword",
      component: () => import("../components/auth/ChangePassword.vue"),
    },
    {
      path: "/profile",
      name: "ChangeProfile",
      component: () => import("../components/auth/ChangeProfile.vue"),
    },
    {
      path: "/register",
      name: "Register",
      component: () => import("../components/auth/Register.vue"),
    },
    {
      path: "/orders",
      name: "Orders",
      component: () => import("../components/orders/Orders.vue"),
    },
    {
      path: "/orders/current",
      name: "CurrentOrder",
      component: () => import("../components/orders/Orders.vue"),
      props: { onlyCurrentOrders: true, ordersTitle: "Current Order" },
    },
    {
      path: "/orders/new",
      name: "NewOrder",
      component: () => import("../components/orders/Order.vue"),
    },
    {
      path: "/products/new",
      name: "newProduct",
      component: () => import("../components/products/Product.vue"),
    },
    {
      path: "/users/new",
      name: "newUser",
      component: () => import("../components/users/User.vue"),
    },
    {
      path: "/orders/:id",
      name: "Order",
      component: () => import("../components/orders/Order.vue"),
      props: (route) => ({ id: parseInt(route.params.id) }),
    },
    {
      path: "/products/:id",
      name: "Product",
      component: () => import("../components/products/Product.vue"),
      props: (route) => ({ id: parseInt(route.params.id) }),
    },
    {
      path: "/forbidden",
      name: "Forbidden",
      component: () => import("@/views/Forbidden.vue"),
    },
    {
      path: "/:pathMatch(.*)*",
      name: "NotFound",
      component: () => import("@/views/NotFound.vue"),
    },
  ],
});

let handlingFirstRoute = true;

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore();
  const orderStore = useOrderStore();

  if (handlingFirstRoute) {
    handlingFirstRoute = false;
    await userStore.restoreToken();
    await orderStore.restoreToken();
  }

  if (to.name == "Dashboard") {
    if (!userStore.user) {
      next({ name: "AnonymousDashboard" });
      return;
    }

    switch (userStore.user.type) {
      case "C":
        if (userStore.user) userStore.loadCustomer(userStore.user);
        next({ name: "CustomerDashboard" });
        return;
        break;

      case "ED":
        next({ name: "DeliveryDashboard" });
        return;
        break;

      case "EC":
        next({ name: "ChefDashboard" });
        return;
        break;
    }
  }

  if (to.name == "Login") {
    next();
    return;
  }

  if (to.name == "newProduct") {
    if (!userStore.user || userStore.user.type != "EM") {
      next({
        name: "Forbidden",
        params: { pathMatch: to.path.substring(1).split("/") },
        query: to.query,
        hash: to.hash,
      });
      return;
    }
  }

  if (to.name == "newUser") {
    if (!userStore.user || userStore.user.type != "EM") {
      next({
        name: "Forbidden",
        params: { pathMatch: to.path.substring(1).split("/") },
        query: to.query,
        hash: to.hash,
      });
      return;
    }
  }

  if (to.name == "Products") {
    if (!userStore.user || userStore.user.type != "EM") {
      next({
        name: "Forbidden",
        params: { pathMatch: to.path.substring(1).split("/") },
        query: to.query,
        hash: to.hash,
      });
      return;
    }
  }

  if (to.name == "Product") {
    if (userStore.user && userStore.user.type == "EM") {
      next();
      return;
    }
    next({
      name: "Forbidden",
      params: { pathMatch: to.path.substring(1).split("/") },
      query: to.query,
      hash: to.hash,
    });
    return;
  }

  if (to.name == "Order") {
    if (
      (userStore.user &&
        (userStore.user.type == "EM" || userStore.user.type == "C")) ||
      !userStore.user
    ) {
      var flag = 0;
      if (!userStore.user) {
        orderStore.restoreToken();
        orderStore.anonymous_orders.forEach((order) => {
          if (order.id == to.params.id) {
            flag = 1;
          }
        });
      }
      if (
        (userStore.user &&
          (userStore.user.type == "C" || userStore.user.type == "EM")) ||
        flag
      ) {
        next();
        return;
      }
    }

    next({
      name: "Forbidden",
      params: { pathMatch: to.path.substring(1).split("/") },
      query: to.query,
      hash: to.hash,
    });
    return;
  }

  if (to.name == "Users") {
    if (userStore.user && userStore.user.type != "EM") {
      next();
      return;
    }
    next({
      name: "Forbidden",
      params: { pathMatch: to.path.substring(1).split("/") },
      query: to.query,
      hash: to.hash,
    });
    return;
  }

  if (to.name == "NewOrder") {
    if (userStore.user === null) {
      next();
      return;
    }
    if (
      userStore.user.type == "EC" ||
      userStore.user.type == "ED" ||
      userStore.user.type == "EM"
    ) {
      next({
        name: "Forbidden",
        params: { pathMatch: to.path.substring(1).split("/") },
        query: to.query,
        hash: to.hash,
      });
      return;
    }
  }

  if (to.name == "User") {
    if (
      userStore.user === null ||
      userStore.user.type != "EM" /*|| userStore.user.id == to.params.id*/
    ) {
      next({
        name: "Forbidden",
        params: { pathMatch: to.path.substring(1).split("/") },
        query: to.query,
        hash: to.hash,
      });
      return;
    }
    if (userStore.user.type == "EM" || userStore.user.id == to.params.id) {
      next();
      return;
    }
  }

  next();
});

export default router;
