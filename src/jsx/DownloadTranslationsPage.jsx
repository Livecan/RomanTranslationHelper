import React, { useMemo, useState } from "react";
import axios from "axios";
import { Stack, TextField, Button, Checkbox } from "@mui/material";
import { DataGrid } from "@mui/x-data-grid";
import { Done } from '@mui/icons-material';

const defaultModuleName = "linkchecker";

export default () => {
  const [moduleName, setModuleName] = useState(defaultModuleName);
  const [localizationData, setLocalizationData] = useState([]);
  const [selectedPhrases, setSelectedPhrase] = useState([]);

  const load = () => {
    axios.get(
      `/roman-translation-helper/download/${moduleName}?format=_json`
    )
      .then(response => {
        setLocalizationData(response.data);
        setSelectedPhrase([]);
      })
      .catch(error => console.error(error));
  };

  // transform localizationData - [languague][phrase] into localizationTable - [phrase][language]
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

  const toggleSelectedPhrase = (phrase) => {
    setSelectedPhrase(selectedPhrases => {
      if (selectedPhrases.includes(phrase)) {
        return selectedPhrases.filter(selectedPhrase => selectedPhrase != phrase);
      }
      else {
        return [...selectedPhrases, phrase];
      }
    })
  }

  const save = () => {
    let data = {};
    for (const language in localizationData) {
      if (localizationData.hasOwnProperty(language)) {
        data[language] = {};
      }
    }

    // get only selected only phrases that were selected for insertion
    for (const [phrase, translationsToInsert] of selectedPhrases.map(selectedPhrase => [selectedPhrase, localizationTable[selectedPhrase]])) {
      // out of those phrases choose only entries that have translation for paticular language
      for (const [language, isToBeInserted] of Object.entries(translationsToInsert)) {
        if (isToBeInserted) {
          data[language][phrase] = localizationData[language][phrase];
        }
      }
    }

    axios.post(
      `/roman-translation-helper/insert-translations/${moduleName}?format=_json`,
      data
    )
      .catch(error => console.error(error));;
  }

  return (
    <Stack direction="column" spacing="1rem">
      <Stack direction="row" spacing="1rem">
        <TextField placeholder="Enter module name" value={moduleName} onChange={e => setModuleName(e.currentTarget.value)} />
        <Button onClick={load}>Search</Button>
      </Stack>
      <DataGrid
        autoHeight
        columns={[
          {
            field: "selector",
            headerName: "",
            width: 20,
            renderCell: (params) => (params.value &&
              <Checkbox checked={selectedPhrases.includes(params.value)} onChange={() => toggleSelectedPhrase(params.value)} />
            )
          },
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
            selector: phrase,
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
      <Button onClick={save}>Save translations</Button>
    </Stack>
  );
};
