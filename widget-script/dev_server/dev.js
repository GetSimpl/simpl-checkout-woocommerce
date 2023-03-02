const express = require("express");
const cors = require("cors");
const fs = require("fs");
const compression = require("compression");

const app = express();

const hostname = "localhost";
const port = 4300;

app.use(
  cors({
    origin: "*",
    credentials: true, //access-control-allow-credentials:true
    optionSuccessStatus: 200,
  })
);

const shouldCompress = (req, res) => {
  return compression.filter(req, res);
};

app.use(
  compression({
    filter: shouldCompress,
    threshold: 0,
  })
);

app.get("/", (req, res) => {
  fs.readFile("../dist/simpl-checkout-widget-v2.iife.js", "utf-8", (err, data) => {
    if (err) {
      console.error(err);
      return;
    }
    res.send(data);
  });
});

app.listen(port, hostname, () => {
  console.log(`Server running at http://${hostname}:${port}/`);
});
