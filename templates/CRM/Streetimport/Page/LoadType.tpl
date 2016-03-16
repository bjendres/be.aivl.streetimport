<div class="crm-content-block crm-block">
  <div id="help">
    {$pageHelpText}
  </div>
  <div class="action-link">
    <a class="button new-option" href="{$addUrl}">
      <span><div class="icon add-icon"></div>{$addButtonLabel}</span>
    </a>
  </div>
  {include file='CRM/common/jsortable.tpl'}
  <div id="load-type-wrapper" class="dataTables_wrapper">
    <table id="load-type-table" class="display">
      <thead>
      <tr>
        <th>{ts}ID{/ts}</th>
        <th>{$loadTypeLabel}</th>
        <th>{$predecessorLabel}</th>
        <th>{$uniqueLabel}</th>
        <th id="nosort"></th>
      </tr>
      </thead>
      <tbody>
      {assign var="rowClass" value="odd-row"}
      {assign var="rowCount" value=0}
      {foreach from=$loadTypes key=loadTypeId item=loadType}
        {assign var="rowCount" value=$rowCount+1}
        <tr id="row{$rowCount}" class={$rowClass}>
          <td>{$loadTypeId}</td>
          <td>{$loadType.label}</td>
          <td>{$loadType.predecessor}</td>
          <td>{$loadType.unique}</td>
          <td><span>{$loadType.action}</span></td>
        </tr>
        {if $rowClass eq "odd-row"}
          {assign var="rowClass" value="even-row"}
        {else}
          {assign var="rowClass" value="odd-row"}
        {/if}
      {/foreach}
      </tbody>
    </table>
  </div>
  <div class="action-link">
    <a class="button new-option" href="{$addUrl}">
      <span><div class="icon add-icon"></div>{$addButtonLabel}</span>
    </a>
  </div>
</div>