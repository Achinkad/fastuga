const http = require("http").createServer();

const io = require("socket.io")(http, {
  cors: {
    origin: "http://localhost:5173",
    methods: ["GET", "POST"],
    credentials: true,
  },
});

http.listen(8080, () => {
  console.log("listening on *:8080");
});

io.on("connection", (socket) => {
  console.log(`client ${socket.id} has connected`);

  socket.on("newOrder", (order) => {
    if (order.status == "R") {
      socket.to("Delivery").emit("newOrder", order);
      console.log("entrou no delivery");
    }
    if (order.status == "P") {
      socket.to("Chef").emit("newOrder", order);
      console.log("entrou no chef");
    }
  });

  socket.on("deliveredOrder", (order) => {
    console.log(order)
    socket.to("Manager").emit("deliveredOrder", order);
  });

  socket.on("deleteOrder", (order) => {
    socket.to("Manager").emit("deleteOrder", order);
  });

  socket.on("updatedOrderChef", (order) => {

    socket.to("Delivery").to("Manager").emit("updatedOrderChef",order);
  });

  socket.on("loggedIn", function (user) {
    socket.emit("loggedIn", user);
    socket.join(user.id);
    if (user.type == "EM") {
      socket.join("Manager");

    }
    if (user.type == "ED") {
      socket.join("Delivery");
    }
    if (user.type == "C") {
      socket.join("Customer");
    }
    if (user.type == "EC") {
      socket.join("Chef");
    } else {
      socket.join("Anonymous");
    }
  });

  socket.on("loggedOut", function (user)
  {
    socket.emit("loggedOut", user);
    socket.leave(user.id);

    if (user.type == "EM") {
      socket.leave("Manager");
    }
    if (user.type == "ED") {
      socket.leave("Delivery");
    }
    if (user.type == "C") {
      socket.leave("Customer");
    }
    if (user.type == "EC") {
      socket.leave("Chef");
    } else {
      socket.leave("Anonymous");
    }
  });

socket.on('updateUser', function (user) {
 socket.in('administrator').except(user.id).emit('updateUser', user)
 socket.in(user.id).emit('updateUser', user)
 })

});
