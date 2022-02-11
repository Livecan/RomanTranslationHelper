import React, { useEffect } from "react";
import axios from "axios";
import { Stack, TextField, Button } from "@mui/material";

export default () => {
  useEffect(() => {
    axios.get(
      "/roman-translation-helper/download/linkchecker?format=_json"
    )
      .then(response => console.log(response.data))
      .catch(error => console.error(error));
  }, []);

  return (
    <Stack direction="row" spacing="1rem">
      <TextField placeholder="Enter module name" />
      <Button>Search</Button>
    </Stack>
  );
};
