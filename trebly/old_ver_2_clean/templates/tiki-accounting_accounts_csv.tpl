{* $Id $ *}{$quote}{tr}Account{/tr}{$quote}{$separator}{$quote}{tr}Account name{/tr}{$quote}{$separator}{$quote}{tr}Notes{/tr}{$quote}{$separator}{$quote}{tr}Currency{/tr}{$quote}{$separator}{$quote}{tr}Budget{/tr}{$quote}{$separator}{$quote}{tr}Locked{/tr}{$quote}{$separator}{$quote}{tr}Debit{/tr}{$quote}{$separator}{$quote}{tr}Credit{/tr}{$quote}{$separator}{$quote}{tr}Tax{/tr}{$quote}{$eol}{foreach from=$accounts" item=a}{$a.accountId}{$separator}{$quote}{$a.accountName|escape}{$quote}{$separator}{$quote}{$a.accountNotes|escape}{$quote}{$separator}{$quote}{$book.bookCurrency}{$quote}{$separator}{$a.accountBudget|currency}{$separator}{$quote}{if $a.accountLocked==1}{tr}Yes{/tr}{else}{tr}No{/tr}{/if}{$quote}{$separator}{$a.debit|currency}{$separator}{$a.credit|currency}{$eol}{/foreach}