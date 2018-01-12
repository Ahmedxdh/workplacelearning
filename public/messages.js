/*!
 *  Lang.js for Laravel localization in JavaScript.
 *
 *  @version 1.1.10
 *  @license MIT https://github.com/rmariuzzo/Lang.js/blob/master/LICENSE
 *  @site    https://github.com/rmariuzzo/Lang.js
 *  @author  Rubens Mariuzzo <rubens@mariuzzo.com>
 */
(function(root,factory){"use strict";if(typeof define==="function"&&define.amd){define([],factory)}else if(typeof exports==="object"){module.exports=factory()}else{root.Lang=factory()}})(this,function(){"use strict";function inferLocale(){if(typeof document!=="undefined"&&document.documentElement){return document.documentElement.lang}}function convertNumber(str){if(str==="-Inf"){return-Infinity}else if(str==="+Inf"||str==="Inf"||str==="*"){return Infinity}return parseInt(str,10)}var intervalRegexp=/^({\s*(\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)\s*})|([\[\]])\s*(-Inf|\*|\-?\d+(\.\d+)?)\s*,\s*(\+?Inf|\*|\-?\d+(\.\d+)?)\s*([\[\]])$/;var anyIntervalRegexp=/({\s*(\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)\s*})|([\[\]])\s*(-Inf|\*|\-?\d+(\.\d+)?)\s*,\s*(\+?Inf|\*|\-?\d+(\.\d+)?)\s*([\[\]])/;var defaults={locale:"en"};var Lang=function(options){options=options||{};this.locale=options.locale||inferLocale()||defaults.locale;this.fallback=options.fallback;this.messages=options.messages};Lang.prototype.setMessages=function(messages){this.messages=messages};Lang.prototype.getLocale=function(){return this.locale||this.fallback};Lang.prototype.setLocale=function(locale){this.locale=locale};Lang.prototype.getFallback=function(){return this.fallback};Lang.prototype.setFallback=function(fallback){this.fallback=fallback};Lang.prototype.has=function(key,locale){if(typeof key!=="string"||!this.messages){return false}return this._getMessage(key,locale)!==null};Lang.prototype.get=function(key,replacements,locale){if(!this.has(key,locale)){return key}var message=this._getMessage(key,locale);if(message===null){return key}if(replacements){message=this._applyReplacements(message,replacements)}return message};Lang.prototype.trans=function(key,replacements){return this.get(key,replacements)};Lang.prototype.choice=function(key,number,replacements,locale){replacements=typeof replacements!=="undefined"?replacements:{};replacements.count=number;var message=this.get(key,replacements,locale);if(message===null||message===undefined){return message}var messageParts=message.split("|");var explicitRules=[];for(var i=0;i<messageParts.length;i++){messageParts[i]=messageParts[i].trim();if(anyIntervalRegexp.test(messageParts[i])){var messageSpaceSplit=messageParts[i].split(/\s/);explicitRules.push(messageSpaceSplit.shift());messageParts[i]=messageSpaceSplit.join(" ")}}if(messageParts.length===1){return message}for(var j=0;j<explicitRules.length;j++){if(this._testInterval(number,explicitRules[j])){return messageParts[j]}}var pluralForm=this._getPluralForm(number);return messageParts[pluralForm]};Lang.prototype.transChoice=function(key,count,replacements){return this.choice(key,count,replacements)};Lang.prototype._parseKey=function(key,locale){if(typeof key!=="string"||typeof locale!=="string"){return null}var segments=key.split(".");var source=segments[0].replace(/\//g,".");return{source:locale+"."+source,sourceFallback:this.getFallback()+"."+source,entries:segments.slice(1)}};Lang.prototype._getMessage=function(key,locale){locale=locale||this.getLocale();key=this._parseKey(key,locale);if(this.messages[key.source]===undefined&&this.messages[key.sourceFallback]===undefined){return null}var message=this.messages[key.source];var entries=key.entries.slice();var subKey="";while(entries.length&&message!==undefined){var subKey=!subKey?entries.shift():subKey.concat(".",entries.shift());if(message[subKey]!==undefined){message=message[subKey];subKey=""}}if(typeof message!=="string"&&this.messages[key.sourceFallback]){message=this.messages[key.sourceFallback];entries=key.entries.slice();subKey="";while(entries.length&&message!==undefined){var subKey=!subKey?entries.shift():subKey.concat(".",entries.shift());if(message[subKey]){message=message[subKey];subKey=""}}}if(typeof message!=="string"){return null}return message};Lang.prototype._findMessageInTree=function(pathSegments,tree){while(pathSegments.length&&tree!==undefined){var dottedKey=pathSegments.join(".");if(tree[dottedKey]){tree=tree[dottedKey];break}tree=tree[pathSegments.shift()]}return tree};Lang.prototype._applyReplacements=function(message,replacements){for(var replace in replacements){message=message.replace(new RegExp(":"+replace,"gi"),function(match){var value=replacements[replace];var allCaps=match===match.toUpperCase();if(allCaps){return value.toUpperCase()}var firstCap=match===match.replace(/\w/i,function(letter){return letter.toUpperCase()});if(firstCap){return value.charAt(0).toUpperCase()+value.slice(1)}return value})}return message};Lang.prototype._testInterval=function(count,interval){if(typeof interval!=="string"){throw"Invalid interval: should be a string."}interval=interval.trim();var matches=interval.match(intervalRegexp);if(!matches){throw"Invalid interval: "+interval}if(matches[2]){var items=matches[2].split(",");for(var i=0;i<items.length;i++){if(parseInt(items[i],10)===count){return true}}}else{matches=matches.filter(function(match){return!!match});var leftDelimiter=matches[1];var leftNumber=convertNumber(matches[2]);if(leftNumber===Infinity){leftNumber=-Infinity}var rightNumber=convertNumber(matches[3]);var rightDelimiter=matches[4];return(leftDelimiter==="["?count>=leftNumber:count>leftNumber)&&(rightDelimiter==="]"?count<=rightNumber:count<rightNumber)}return false};Lang.prototype._getPluralForm=function(count){switch(this.locale){case"az":case"bo":case"dz":case"id":case"ja":case"jv":case"ka":case"km":case"kn":case"ko":case"ms":case"th":case"tr":case"vi":case"zh":return 0;case"af":case"bn":case"bg":case"ca":case"da":case"de":case"el":case"en":case"eo":case"es":case"et":case"eu":case"fa":case"fi":case"fo":case"fur":case"fy":case"gl":case"gu":case"ha":case"he":case"hu":case"is":case"it":case"ku":case"lb":case"ml":case"mn":case"mr":case"nah":case"nb":case"ne":case"nl":case"nn":case"no":case"om":case"or":case"pa":case"pap":case"ps":case"pt":case"so":case"sq":case"sv":case"sw":case"ta":case"te":case"tk":case"ur":case"zu":return count==1?0:1;case"am":case"bh":case"fil":case"fr":case"gun":case"hi":case"hy":case"ln":case"mg":case"nso":case"xbr":case"ti":case"wa":return count===0||count===1?0:1;case"be":case"bs":case"hr":case"ru":case"sr":case"uk":return count%10==1&&count%100!=11?0:count%10>=2&&count%10<=4&&(count%100<10||count%100>=20)?1:2;case"cs":case"sk":return count==1?0:count>=2&&count<=4?1:2;case"ga":return count==1?0:count==2?1:2;case"lt":return count%10==1&&count%100!=11?0:count%10>=2&&(count%100<10||count%100>=20)?1:2;case"sl":return count%100==1?0:count%100==2?1:count%100==3||count%100==4?2:3;case"mk":return count%10==1?0:1;case"mt":return count==1?0:count===0||count%100>1&&count%100<11?1:count%100>10&&count%100<20?2:3;case"lv":return count===0?0:count%10==1&&count%100!=11?1:2;case"pl":return count==1?0:count%10>=2&&count%10<=4&&(count%100<12||count%100>14)?1:2;case"cy":return count==1?0:count==2?1:count==8||count==11?2:3;case"ro":return count==1?0:count===0||count%100>0&&count%100<20?1:2;case"ar":return count===0?0:count==1?1:count==2?2:count%100>=3&&count%100<=10?3:count%100>=11&&count%100<=99?4:5;default:return 0}};return Lang});

(function () {
    Lang = new Lang();
    Lang.setMessages({"en.react":{"acting":"Acting","add":"Add","add-cohort":"Add cohort","aid":"Support","categories":"Categories","category":"Category","cohort-delete":"Delete cohort","cohort-delete-block":"Cohort has workplace learning periods and therefore cannot be deleted","cohort-desc":"Cohort description","cohort-details":"Cohort details","cohort-disable":"Disable program for new workplace learning periods","cohort-enable":"Enable cohort for new workplace learning periods","cohort-name":"Cohort name","cohorts":"Cohorts","competence":"Competence","competence-description":"Competence description","competencies":"Competencies","complexity":"Complexity","current-description":"Current description","date":"Date","delete":"Delete","delete-confirm":"Are you sure you want to delete this activity?","description":"Description","download":"download","educationprogram":"Education program","educprogram-add":"Add program","educprogram-manage":"Manage and create programs","educprogram-name":"Program name","enddate":"End date","evidence":"Evidence","export":"Export","export-to":"Export to","filters":"Filters","learningpoints-followup":"Learning points and followup","learningquestion":"Learning question","mail":{"failed":"Something went wrong, try again later","sending":"Sending...","sent":"The email has been sent"},"mail-to":"Mail to","none":"none","none-selected":"None selected","producing":"Producing","program-delete":"Delete program","program-delete-blocked":"Program has cohorts, therefore it cannot be deleted","program-details":"Program details","program-disable":"Disable program for new students","program-enable":"Enable program for new students","remove":"remove","resourceperson":"Resource person","save":"Save","situation":"Situatie","startdate":"Starting date","statistic":{"create":"Create","create-statistic":"Create new statistic","errors":{"empty-variable-parameter":"Parameter for a variable cannot be empty","name":"Name of a statistic cannot be empty"},"parameter":"Parameter","save":"Save","select-operator":"Select operator","select-variable-one":"Select first variable","select-variable-two":"Select second variable","statistic-name":"Name for new statistic"},"status":"Status","theory":"Theory","time":"Duration","upload-instructions":"Click or drop file to upload the competence description","with-whom":"With whom?"},"nl.react":{"acting":"Acting","add":"Toevoegen","add-cohort":"Cohort toevoegen","aid":"Hulpbron","categories":"Categorie\u00ebn","category":"Categorie","cohort-delete":"Cohort verwijderen","cohort-delete-block":"Cohort heeft stageperiodes gekoppeld en kan dus niet verwijderd worden","cohort-desc":"Cohort omschrijving","cohort-details":"Cohortdetails","cohort-disable":"Cohort uitzetten voor nieuwe stageperiodes","cohort-enable":"Cohort aanzetten voor nieuwe stageperiodes","cohort-name":"Cohort naam","cohorts":"Cohorten","competence":"Competentie","competence-description":"Competentie omschrijving","competencies":"Competenties","complexity":"Complexiteit","current-description":"Huidige omschrijving","date":"Datum","delete":"Verwijderen","delete-confirm":"Weet je zeker dat je deze activiteit wilt verwijderen?","description":"Omschrijving","download":"download","educationprogram":"Opleiding","educprogram-add":"Opleiding toevoegen","educprogram-manage":"Beheer en cree\u00ebr opleidingen","educprogram-name":"Naam opleiding","enddate":"Einddatum","evidence":"Bewijsstuk","export":"Exporteer","export-to":"Export naar","filters":"Filters","learningpoints-followup":"Leerpunten en vervolg","learningquestion":"Leervraag","mail":{"failed":"Er is iets misgegaan bij het verzenden, probeer het later nog eens","sending":"Bezig met verzenden","sent":"De email is successvol verzonden"},"mail-to":"Mailen naar","none":"geen","none-selected":"Geen geselecteerd","producing":"Producing","program-delete":"Verwijder opleiding","program-delete-blocked":"Opleiding heeft cohorten gekoppeld en kan dus niet verwijderd worden","program-details":"Programmadetails","program-disable":"Opleiding uitzetten voor nieuwe studenten","program-enable":"Opleiding aanzetten voor nieuwe studenten","remove":"verwijderen","resourceperson":"Bronpersoon","save":"Opslaan","situation":"Situatie","startdate":"Startdatum","statistic":{"create":"Maak aan","create-statistic":"Maak nieuwe statistiek aan","errors":{"empty-variable-parameter":"De parameter voor een variabele mag niet leeg zijn","name":"De naam van de statistiek mag niet leeg zijn"},"parameter":"Parameter","save":"Opslaan","select-operator":"Selecteer operator","select-variable-one":"Selecteer de eerste variabele","select-variable-two":"Selecteer de tweede variabele","statistic-name":"Naam voor nieuwe statistiek"},"status":"Status","theory":"Theorie","time":"Tijd","upload-instructions":"Klik hier of sleep een bestand om te uploaden","with-whom":"Met wie?"}});
})();
