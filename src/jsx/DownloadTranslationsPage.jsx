import React, { useMemo, useState } from "react";
import axios from "axios";
import { Stack, TextField, Button } from "@mui/material";
import { DataGrid } from "@mui/x-data-grid";
import { Done } from '@mui/icons-material';

export default () => {
  const [moduleName, setModuleName] = useState(null);
  const [localizationData, setLocalizationData] = useState([]);

  const load = () => {
    axios.get(
      `/roman-translation-helper/download/${moduleName}?format=_json`
    )
      .then(response => {
        setLocalizationData(response.data);
      })
      .catch(error => console.error(error));
  };

  const localizationTable = useMemo(() => {
      let table = {};
      for (const [language, languageData] of Object.entries(localizationData)) {
        for (const phraseId in languageData) {
          if (Object.hasOwnProperty.call(languageData, phraseId)) {
            if (!table[phraseId]) {
              table[phraseId] = {};
            }
            table[phraseId][language] = true;

          }
        }
      }
      return table;
    },
    [localizationData]
  );

  return (
    <Stack direction="column" spacing="1rem">
      <Stack direction="row" spacing="1rem">
        <TextField placeholder="Enter module name" onChange={e => setModuleName(e.currentTarget.value)} />
        <Button onClick={load}>Search</Button>
      </Stack>
      <DataGrid
        autoHeight
        columns={[
          {
            field: "phrase",
            headerName: "Phrase",
            flex: 1,
          },
          ...(Object.keys(localizationData).map(language => (
            {
              field: language === "id" ? "_id" : language,
              headerName: language,
              width: 20,
              renderCell: (params) => (
                params.value &&
                  <Done />
              )
            }
          )))
          ]
        }
        rows={Object.entries(localizationTable).map(([phrase, translations]) => {
          let row = {
            id: phrase,
            phrase: phrase,
          }
          for (const language in translations) {
            if (Object.hasOwnProperty.call(translations, language)) {
              row[language === "id" ? "_id" : language] = 1;
            }
          }
          return row;
        })}
      />
    </Stack>
  );
};
