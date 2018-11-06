/* Global variables */
var Rules;
var VariantList;

var curLine;
var previous;
var ArtiScore;
var PolyScore;
var ArtiProt;
var PolyProt;
var Result;

/* Specific variables */
var AllSamples = 0;
var NrNonClinicalDBs;
var NrClinicalDBs;
var NrAnyDBs;
var special;


/* Helper functions */
if(!Array.isArray) {
  Array.isArray = function (vArg) {
    return Object.prototype.toString.call(vArg) === "[object Array]";
  };
}

function trim(str) {
  return str.replace(/^\s+|\s+$/gm,'');
}

function isEmpty(input) {
  return (typeof(input) === "undefined" || input == null || (typeof(input) == "string" && (input == " " || input == "" || input == ".")));
}

function strLength(input) {
  if (input != null)
    return input.length;
  else return 0;
}

function stringContains(haystack, needle) {
  if (haystack != null)
    return (haystack.indexOf(needle) >= 0);
  else return false;
}

function stringConcat(first, second) {
  if (first != null)
    if (second != null)
      return (first + second);
    else
      return first;
  else
    return second;
}


function parseCondition(expression) {
  if (typeof(expression) !== "undefined") {
    expression = expression.replace(/\:\=/g, '=');
    expression = expression.replace(/min\(/g, 'Math.min(');
    expression = expression.replace(/max\(/g, 'Math.max(');
    var func = new Function('t', 'return ' + expression + ';');
    return func;
  }
}

/* Read the JSON and create FilterObject */
function loadJSON(response)
{
  function preprocEntry(cur)
  {
    cur.Condition = parseCondition(cur.Condition);
    if (typeof(cur.Checkbox) != "undefined")
      cur["Checked"] = true;
    if (typeof(cur.Default) != "undefined")
    {
      if (Array.isArray(cur.Default))
        cur["Param"] = cur.Default.slice();
      else
        cur["Param"] = cur.Default;
    }
    if (typeof(cur.ArtiScore) != "undefined")
      cur["ArtiDefault"] = cur.ArtiScore;
    if (typeof(cur.PolyScore) != "undefined")
      cur["PolyDefault"] = cur.PolyScore;
  }

  try {
    Rules = eval(response);  // JSON.parse erfordert Deaktivierung der Kompatibilitätsansicht für Intranetseiten im IE
    
    for (var cat = 0; cat < Rules.length; cat++)
    {
      for (var i = 0; i < Rules[cat].Entries.length; i++)
      {
        var cur = Rules[cat].Entries[i];
        preprocEntry(cur);
        
        if (typeof(cur.Type) != "undefined" && (cur.Type == "AND-Node" || cur.Type == "OR-Node"))
        {
          for (j = 0; j < cur.Entries.length; j++)
            preprocEntry(cur.Entries[j]);
        }
      }
    }
    return true;

  }
  catch (err) {
    return false;
  }
}


function increase(curID, incBy)
{
  var curScore = document.getElementById(curID);
  if (curScore) {
    var newVal = ((parseInt(curScore.innerHTML) || 0) + incBy);
    curScore.innerHTML = (newVal > 0 ? "+" : "") + newVal;
  }
}

function checkAllChildren(curID, topID)
{
  var topCheck = document.getElementById("check-" + topID);
  if (topCheck)
  {
    if (curID != topID)
      topCheck.checked = true;
    else
    {
      if (!topCheck.checked)
      {      
        var children = document.getElementById(curID);
        if (children) children = children.getElementsByTagName("input");
        if (children)
        {
          for (var i = 0; i < children.length; i++) {
            if (children[i].type == "checkbox") children[i].checked = false;
          }
        }
      }
    }
  }
}

function createGUI()
{
  var GUIHtml = "<form>";

  function printEntry(cur, curID, topID)
  {
    if (typeof(cur.Checkbox) != "undefined" && cur.Checkbox == true)
      GUIHtml += '<input type="checkbox" id="check-' + curID + '"' + (cur.Checked ? ' checked' : '') + ' onclick=checkAllChildren("' + curID + '","' + topID + '")>&nbsp;';

    if (typeof(cur.Caption) != "undefined")
    {
      var caption = cur.Caption;
      if (typeof(cur.Param) != "undefined" && (!isNaN(cur.Param) || Array.isArray(cur.Param)))  {
        if (isNaN(cur.Param)) {
          for (var k = 0; k < cur.Param.length; k++)
            caption = caption.replace(/%+(f|d)/, '<input type="text" class="form-control" name="" id="param-' + curID + '-' + k + '" value="' + cur.Param[k] + '" maxlength="6" size="5">');
        }
        else
          caption  = caption.replace(/%+(f|d)/g, '<input type="text" class="form-control" name="" id="param-' + curID           + '" value="' + cur.Param    + '" maxlength="6" size="5">');
      }
      GUIHtml += '<span class="filter-caption">' + caption + '</span>';
    }

    GUIHtml += '<span class="filter-scores">';
    if (typeof(cur.ArtiScore) != "undefined")

      GUIHtml += '<a onclick=increase("arti-' + curID + '",1) oncontextmenu=increase("arti-' + curID + '",-1);return(false) style="cursor: pointer" title="Click to increase score, right-click to decrease."><span class="filter-artiscore" id="arti-' + curID + '">' + (cur.ArtiScore>0 ? '+' : '') + (cur.ArtiScore!=0 ? cur.ArtiScore : '') + '</span></a>';
    if (typeof(cur.PolyScore) != "undefined")
      GUIHtml += '<a onclick=increase("poly-' + curID + '",1) oncontextmenu=increase("poly-' + curID + '",-1);return(false) style="cursor: pointer" title="Click to increase score, right-click to decrease."><span class="filter-polyscore" id="poly-' + curID + '">' + (cur.PolyScore>0 ? '+' : '') + (cur.PolyScore!=0 ? cur.PolyScore : '') + '</span></a>';
    GUIHtml += '</span>';  // filter-scores
  }

  GUIHtml = '<div class="filters">'
  GUIHtml += '<div class="filter-block" style="padding-bottom: 5px"><span class="filter-scores">'
  GUIHtml += '<span class="filter-artiscore">Arti</span> <span class="filter-polyscore">Poly</span></span></div>';
  for (var cat = 0; cat < Rules.length; cat++)
  {
    GUIHtml += '<div class="filter-block">';
    GUIHtml += '<div class="filter-category" style="background-color: ' + Rules[cat].Color + '">' + Rules[cat].Caption + '</div>';

    for (var i = 0; i < Rules[cat].Entries.length; i++)
    {
      var cur = Rules[cat].Entries[i];
      var curID = cat + "-" + i;
      GUIHtml += '<div class="filter-entry ' + cur.Type + '" id="' + curID + '">';
      printEntry(cur, curID, curID);
      
      if (typeof(cur.Entries) != "undefined") {
        for (var j = 0; j < cur.Entries.length; j++)
        {
          var sub = cur.Entries[j];
          var subID = curID + "-" + j;
          GUIHtml += '<div class="filter-subentry">'
          printEntry(sub, subID, curID);
          GUIHtml += '</div>';  // filter-subentry
        }
      }
      GUIHtml += '</div>';  // filter-entry
    }
    GUIHtml += '</div>';  // filter-block
  }
  GUIHtml += '</div>';  // filters
  GUIHtml += '<div class="filter-buttons"><button type="button" id="btnLoadScheme" class="btn btn-default" onClick="loadJSON2()">Load scheme...</button><button type="button" id="btnReset" class="btn btn-default" onClick="resetGUI()">Reset</button><button type="button" id="btnCalc" class="btn btn-default" onClick="applyFilters2()">Calculate scores</button></div></form>';

  document.getElementById("GUI").innerHTML = GUIHtml;
  return GUIHtml;
}

function readFromGUI()
{
  function checkFloat(input)
  {
    var asFloat = parseFloat(input);
    if (input == "null")
      return null;
    else
      return (!isNaN(asFloat) && (input.length - asFloat.toString().length < 4) ? asFloat : input);
  }

  function readEntry(cur, curID)
  {
    var elem;
  
    if (typeof(cur.Checkbox) != "undefined" && cur.Checkbox == true)
    {
      elem = document.getElementById("check-" + curID);
      if(elem) cur.Checked = elem.checked;
    }
    if (typeof(cur.ArtiScore) != "undefined")
    {
      elem = document.getElementById("arti-" + curID);
      if(elem) cur.ArtiScore = (parseInt(elem.innerText) || 0);
    }
    if (typeof(cur.PolyScore) != "undefined")
    {
      elem = document.getElementById("poly-" + curID);
      if(elem) cur.PolyScore = (parseInt(elem.innerText) || 0);
    }
    if (typeof(cur.Param) != "undefined" && (!isNaN(cur.Param) || Array.isArray(cur.Param)))  {
      if (isNaN(cur.Param)) {
        for (var k = 0; k < cur.Param.length; k++)
        {
          elem = document.getElementById("param-" + curID + "-" + k);
          if(elem) cur.Param[k] = checkFloat(elem.value);
        }
      }
      else
      {
        elem = document.getElementById("param-" + curID);
        if(elem) cur.Param = checkFloat(elem.value);
      }
    }
  }

  for (var cat = 0; cat < Rules.length; cat++)
  {
    for (var i = 0; i < Rules[cat].Entries.length; i++)
    {
      var cur = Rules[cat].Entries[i];
      readEntry(cur, cat + "-" + i);
      
      if (typeof(cur.Entries) != "undefined") {
        for (var j = 0; j < cur.Entries.length; j++)
          readEntry(cur.Entries[j], cat + "-" + i + "-" + j);
      }
    }
  }
}

/* Reset GUI to default values */
function resetGUI()
{
  function resetEntry(cur)
  {
    if (typeof(cur.Param) != "undefined" && typeof(cur.Default) != "undefined")
    {
      if (Array.isArray(cur.Default))
        cur["Param"] = cur.Default.slice();
      else
        cur["Param"] = cur.Default;
    }
    if (typeof(cur.ArtiScore) != "undefined" && typeof(cur.ArtiDefault) != "undefined")
      cur.ArtiScore = cur.ArtiDefault;
    if (typeof(cur.PolyScore) != "undefined" && typeof(cur.PolyDefault) != "undefined")
      cur.PolyScore = cur.PolyDefault;
    if (typeof(cur.Checkbox) != "undefined")
      cur.Checked = true;
  }

  for (var cat = 0; cat < Rules.length; cat++)
  {
    for (var i = 0; i < Rules[cat].Entries.length; i++)
    {
      var curEntry = Rules[cat].Entries[i];
      resetEntry(curEntry);
      
      if (typeof(curEntry.Entries) != "undefined") {
        for (var j = 0; j < curEntry.Entries.length; j++)
          resetEntry(curEntry.Entries[j]);
      }
    }
  }
  createGUI();
}


/* Apply filters to variants */
function applyFilters()
{
  try {
    for (curLine = 0; curLine < VariantList.length; curLine++)
    {
      NrNonClinicalDBs = 0;
      NrClinicalDBs = 0;
      NrAnyDBs = 0;
      previous = false;
      special  = false;
      ArtiScore = 0;
      PolyScore = 0;
      ArtiProt = '';
      PolyProt = '';
      Result = "Probably True";

      for (var cat = 0; cat < Rules.length; cat++)
      {
        for (var i = 0; i < Rules[cat].Entries.length; i++)
        {
          var cur = Rules[cat].Entries[i];
          if ((typeof(cur.Checkbox) === "undefined") || cur.Checked)
          {
            //OR-Node:
            if (cur.Type == "OR-Node") {
              for (var j = 0; j < cur.Entries.length; j++)
              {
                if (previous = cur.Entries[j].Condition(cur.Entries[j].Param))
                  break;
              }
              if (j = 0 || previous == false)
                continue;
            } else {
              previous = cur.Condition(cur.Param);
            }
            
            if (previous)
            {
              if ((typeof(cur.ArtiScore) != "undefined") && (cur.ArtiScore != 0))
              {
                ArtiScore += cur.ArtiScore;
                ArtiProt  += (ArtiProt != "" ? ", " : "") + (cur.ArtiScore > 0 ? "+" : "") + cur.ArtiScore + ": " + cur.Protocoll;
              }
              if ((typeof(cur.PolyScore) != "undefined") && (cur.PolyScore != 0))
              {
                PolyScore += cur.PolyScore;
                PolyProt  += (PolyProt != "" ? ", " : "") + (cur.PolyScore > 0 ? "+" : "") + cur.PolyScore + ": " + cur.Protocoll;
              }
              if (typeof(cur.Result) != "undefined")
                Result = cur.Result;
            }
            
            //AND-Node:
            if (cur.Type == "AND-Node") {
              var curAND = previous;
              for (var j = 0; j < cur.Entries.length; j++)
              {
                if (previous = curAND && cur.Entries[j].Condition(cur.Entries[j].Param))
                {
                  if ((typeof(cur.Entries[j].ArtiScore) != "undefined") && (cur.Entries[j].ArtiScore != 0))
                  {
                    ArtiScore += cur.Entries[j].ArtiScore;
                    ArtiProt  += (ArtiProt != "" ? ", " : "") + (cur.Entries[j].ArtiScore > 0 ? "+" : "") + cur.Entries[j].ArtiScore + ": " + cur.Entries[j].Protocoll;
                  }
                  if ((typeof(cur.Entries[j].PolyScore) != "undefined") && (cur.Entries[j].PolyScore != 0))
                  {
                    PolyScore += cur.Entries[j].PolyScore;
                    PolyProt  += (PolyProt != "" ? ", " : "") + (cur.Entries[j].PolyScore > 0 ? "+" : "") + cur.Entries[j].PolyScore + ": " + cur.Entries[j].Protocoll;
                  }
                  if (typeof(cur.Entries[j].Result) != "undefined")
                    Result = cur.Entries[j].Result;
                }
              }
            }
          }
        }
      }
      
      VariantList[curLine]["newResult"] = Result;
      VariantList[curLine]["newArtiScore"] = ArtiScore;
      VariantList[curLine]["newPolyScore"] = PolyScore;
      VariantList[curLine]["newArtiProt"] = ArtiProt;
      VariantList[curLine]["newPolyProt"] = PolyProt;
      
      if (typeof(debug) != "undefined" && debug == true)
      {
        if (VariantList[curLine].newPolyScore != VariantList[curLine].GTPolyScore)
        {
          console.log("chr=" + VariantList[curLine].chr + ", pos=" + VariantList[curLine].pos + ", ref=" + VariantList[curLine].ref + ", alt=" + VariantList[curLine].alt + ": GTPolyScore=" + VariantList[curLine].GTPolyScore + ", newPolyScore=" + VariantList[curLine].newPolyScore);
          console.log("old: " + VariantList[curLine].GTPolyProt);
          console.log("new: " + VariantList[curLine].newPolyProt);
          console.log("");
        }
      }
    }
    return true;
  }
  catch (err) {
    return false;
  }
}
