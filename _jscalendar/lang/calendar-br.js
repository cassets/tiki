// ** I18N
Calendar._DN = new Array
("Domingo",
 "Segunda",
 "Ter�a",
 "Quarta",
 "Quinta",
 "Sexta",
 "S�bado",
 "Domingo");

Calendar._SDN = new Array
("Dom",
 "Seg",
 "Ter",
 "Qua",
 "Qui",
 "Sex",
 "Sab",
 "Dom");

Calendar._MN = new Array
("Janeiro",
 "Fevereiro",
 "Mar�o",
 "Abril",
 "Maio",
 "Junho",
 "Julho",
 "Agosto",
 "Setembro",
 "Outubro",
 "Novembro",
 "Dezembro");

Calendar._SMN = new Array
("Jan",
 "Fev",
 "Mar",
 "Abr",
 "Mai",
 "Jun",
 "Jul",
 "Ago",
 "Set",
 "Out",
 "Nov",
 "Dez");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Sobre o calend�rio";

Calendar._TT["ABOUT"] =
"Seletor de Data/Hora DHTML\n" +
"(c) dynarch.com 2002-2003\n" + // don't translate this this ;-)
"Para �ltima vers�o visite: http://dynarch.com/mishoo/calendar.epl\n" +
"Distribu�da sob GNU LGPL.  Veja http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"Sele��o de data:\n" +
"- Use os bot�es \xab e \xbb para selecionar o ano\n" +
"- Use os bot�es " + String.fromCharCode(0x2039) + " e " + String.fromCharCode(0x203a) + " para selecionar o m�s\n" +
"- Pressione o bot�o do mouse em quaisquer bot�es acima para sele��o mais r�pida..";

Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Sele��o de hora:\n" +
"- Clique em qualquer parte do hor�rio para aument�-la\n" +
"- ou Shift e clique para diminu�-la\n" +
"- ou clique e arraste para sele��o mais r�pida.";

Calendar._TT["TOGGLE"] = "Altera primeiro dia da semana";
Calendar._TT["PREV_YEAR"] = "Ano anterior(pressione para menu)";
Calendar._TT["PREV_MONTH"] = "M�s anterior (pressione bot�o para menu)";
Calendar._TT["GO_TODAY"] = "Usar data atual";
Calendar._TT["NEXT_MONTH"] = "M�s seguinte (pressione bot�o para menu)";
Calendar._TT["NEXT_YEAR"] = "ano seguinte (pressione bot�o para menu)";
Calendar._TT["SEL_DATE"] = "Selecione uma data";
Calendar._TT["DRAG_TO_MOVE"] = "Arrasta calend�rio";
Calendar._TT["PART_TODAY"] = " (hoje)";
Calendar._TT["MON_FIRST"] = "Inicia na segunda-feira";
Calendar._TT["SUN_FIRST"] = "Inicia no domingo";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Mostra %s primeiro(s)";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Fechar";
Calendar._TT["TODAY"] = "Hoje";
Calendar._TT["TIME_PART"] = "(Shift-)Clique ou arraste para trocar valor";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d/%m/%Y";
//Calendar._TT["TT_DATE_FORMAT"] = "%A, %b %e";

Calendar._TT["WK"] = "wk";
Calendar._TT["TIME"] = "Hor�rio:";



Calendar._TT["CLOSE"] = "Fechar";
Calendar._TT["TODAY"] = "Hoje";

// date formats
//Calendar._TT["DEF_DATE_FORMAT"] = "dd-mm-y";
//Calendar._TT["TT_DATE_FORMAT"] = "DD, dd de MM de y";

Calendar._TT["WK"] = "sem";
