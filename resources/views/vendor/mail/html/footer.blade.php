<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center">
                    @if (trim($slot ?? '') !== '')
                        {{ Illuminate\Mail\Markdown::parse($slot) }}
                    @endif

                    <p style="font-size: 12px; color: #999; text-align: center; margin-top: 8px;">
                        © {{ date('Y') }} TL GOLD COMPUTER. All rights reserved.
                    </p>
                </td>
            </tr>
        </table>
    </td>
</tr>
